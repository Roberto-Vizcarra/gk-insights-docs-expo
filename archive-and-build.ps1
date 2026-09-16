<#
.SYNOPSIS
    Archive the current plugin zip by version, then build the new one.

.DESCRIPTION
    Keeps a rollback trail in plugin-archive/. The version is read from the
    gki-docs-helper.php header INSIDE the zip being archived (not the working
    tree), so the archived file is named for what it actually contains, even
    if the header on disk has already been bumped for the next release.

    Then runs build-plugin.py, which is the only sanctioned way to build the
    zip (see CLAUDE.md), and reports the version of the result.

    This file is deliberately plain ASCII: Windows PowerShell 5.1 reads a
    BOM-less script as ANSI, and any non-ASCII character in a string breaks
    the parser.

.PARAMETER FromRef
    Archive the zip as committed at this git ref (e.g. main, v1.14.2-stable)
    instead of the one in the working tree. Use this to seed the archive with
    what production is actually running.

.PARAMETER SkipBuild
    Archive only; do not run build-plugin.py.

.PARAMETER Force
    Overwrite an existing archive with the same version and different
    contents. Without it, a differing duplicate is written with a timestamp
    suffix.

.EXAMPLE
    .\archive-and-build.ps1
    .\archive-and-build.ps1 -SkipBuild
    .\archive-and-build.ps1 -FromRef main
#>
[CmdletBinding()]
param(
    [string]$FromRef,
    [switch]$SkipBuild,
    [switch]$Force
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0

$Root       = Split-Path -Parent $MyInvocation.MyCommand.Path
$PluginDir  = Join-Path $Root 'gki-docs-helper'
$ZipPath    = Join-Path $PluginDir 'gki-docs-helper.zip'
$ArchiveDir = Join-Path $Root 'plugin-archive'
$Builder    = Join-Path $Root 'build-plugin.py'
$Ledger     = Join-Path $ArchiveDir 'ARCHIVE.md'

function Write-Step([string]$msg) { Write-Host "==> $msg" -ForegroundColor Cyan }
function Write-Ok([string]$msg)   { Write-Host "    OK  $msg" -ForegroundColor Green }
function Write-Warn2([string]$msg){ Write-Host "    !!  $msg" -ForegroundColor Yellow }
function Get-KB([string]$path)    { [math]::Round((Get-Item $path).Length / 1KB) }

# --- Read the plugin version from the PHP header inside a zip ---------------
function Get-ZipPluginVersion([string]$zip) {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $archive = [System.IO.Compression.ZipFile]::OpenRead($zip)
    try {
        $entry = $archive.Entries | Where-Object { $_.FullName -eq 'gki-docs-helper/gki-docs-helper.php' } | Select-Object -First 1
        if (-not $entry) {
            throw "Zip has no gki-docs-helper/gki-docs-helper.php entry - not a valid plugin archive."
        }
        $stream = $entry.Open()
        $reader = New-Object System.IO.StreamReader($stream)
        try     { $php = $reader.ReadToEnd() }
        finally { $reader.Dispose(); $stream.Dispose() }
    }
    finally { $archive.Dispose() }

    $headerRx   = '(?m)^\s*\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)'
    $constantRx = "define\(\s*'GKI_DOCS_VERSION'\s*,\s*'([0-9]+\.[0-9]+\.[0-9]+)'"
    $header   = [regex]::Match($php, $headerRx)
    $constant = [regex]::Match($php, $constantRx)

    if (-not $header.Success)   { throw "Could not read 'Version:' from the plugin header inside the zip." }
    if (-not $constant.Success) { throw "Could not read GKI_DOCS_VERSION from the plugin file inside the zip." }

    $h = $header.Groups[1].Value
    $c = $constant.Groups[1].Value
    if ($h -ne $c) {
        throw "Header version $h and GKI_DOCS_VERSION $c disagree inside the zip. Refusing to archive an inconsistent build."
    }
    return $h
}

function Get-FileSha256([string]$path) {
    (Get-FileHash -Path $path -Algorithm SHA256).Hash
}

function Get-GitShort([string]$ref) {
    $prev = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try   { $out = & git -C $Root rev-parse --short $ref 2>$null }
    finally { $ErrorActionPreference = $prev }
    if ($LASTEXITCODE -ne 0 -or -not $out) { return 'n/a' }
    return ($out | Select-Object -First 1).Trim()
}

# --- 1. Locate the zip to archive -------------------------------------------
$SourceZip   = $ZipPath
$SourceLabel = 'working tree'
$TempZip     = $null

if ($FromRef) {
    Write-Step "Extracting plugin zip as committed at '$FromRef'"
    $TempZip = Join-Path ([System.IO.Path]::GetTempPath()) ("gki-docs-helper-" + [guid]::NewGuid().ToString('N').Substring(0, 8) + ".zip")

    # PowerShell's own redirection is text-mode and corrupts binaries.
    # cmd.exe's redirection is byte-exact, so route git's stdout through it.
    $spec = "${FromRef}:gki-docs-helper/gki-docs-helper.zip"
    & cmd /c "git -C `"$Root`" show `"$spec`" > `"$TempZip`""
    if ($LASTEXITCODE -ne 0 -or -not (Test-Path $TempZip) -or (Get-Item $TempZip).Length -lt 1024) {
        throw "Could not extract gki-docs-helper/gki-docs-helper.zip from ref '$FromRef'."
    }
    $SourceZip   = $TempZip
    $SourceLabel = "ref $FromRef"
}

# --- 2. Archive -------------------------------------------------------------
Write-Step "Archiving plugin zip ($SourceLabel)"

$OldVersion = $null
$Archived   = $null

if (-not (Test-Path $SourceZip)) {
    Write-Warn2 "No zip at $SourceZip - nothing to archive."
}
else {
    $OldVersion = Get-ZipPluginVersion $SourceZip
    if (-not (Test-Path $ArchiveDir)) { New-Item -ItemType Directory -Path $ArchiveDir | Out-Null }

    $Target  = Join-Path $ArchiveDir "gki-docs-helper-v$OldVersion.zip"
    $NewHash = Get-FileSha256 $SourceZip

    if (Test-Path $Target) {
        $ExistingHash = Get-FileSha256 $Target
        if ($ExistingHash -eq $NewHash) {
            Write-Ok "v$OldVersion already archived with identical contents - skipped."
            $Archived = $Target
        }
        elseif ($Force) {
            Copy-Item $SourceZip $Target -Force
            Write-Warn2 "v$OldVersion existed with different contents - overwritten (-Force)."
            $Archived = $Target
        }
        else {
            $Stamp  = Get-Date -Format 'yyyyMMdd-HHmmss'
            $Target = Join-Path $ArchiveDir "gki-docs-helper-v$OldVersion-$Stamp.zip"
            Copy-Item $SourceZip $Target
            Write-Warn2 "v$OldVersion existed with different contents - saved as $(Split-Path -Leaf $Target). Same version, two builds: check which one is on prod."
            $Archived = $Target
        }
    }
    else {
        Copy-Item $SourceZip $Target
        Write-Ok "Archived v$OldVersion ($SourceLabel) -> plugin-archive\$(Split-Path -Leaf $Target) ($(Get-KB $Target) KB)"
        $Archived = $Target
    }

    # A small ledger next to the zips so the folder explains itself.
    if (-not (Test-Path $Ledger)) {
        $Head = @(
            '# Plugin archive',
            '',
            'Rollback copies of gki-docs-helper.zip, one per version, written by',
            '`archive-and-build.ps1` before each build. To roll back: upload the zip',
            'through WP Admin -> Plugins -> Add New -> Upload Plugin (replace existing),',
            'then hard-refresh; the version string busts the CSS/JS cache.',
            '',
            '| Archived (local time) | Version | File | SHA-256 (first 12) | Source commit |',
            '| --- | --- | --- | --- | --- |'
        )
        Set-Content -Path $Ledger -Value $Head -Encoding UTF8
    }

    $RefName  = if ($FromRef) { $FromRef } else { 'HEAD' }
    $ShortSha = Get-GitShort $RefName
    $Source   = if ($FromRef) { "$FromRef@$ShortSha" } else { $ShortSha }
    $Leaf     = Split-Path -Leaf $Archived
    $Row      = "| $(Get-Date -Format 'yyyy-MM-dd HH:mm') | $OldVersion | $Leaf | $($NewHash.Substring(0, 12)) | $Source |"

    if (-not (Select-String -Path $Ledger -SimpleMatch -Pattern $Leaf -Quiet)) {
        Add-Content -Path $Ledger -Value $Row -Encoding UTF8
    }
}

if ($TempZip -and (Test-Path $TempZip)) { Remove-Item $TempZip -Force }

# --- 3. Build ---------------------------------------------------------------
if ($SkipBuild) {
    Write-Step "Skipping build (-SkipBuild)"
    exit 0
}

Write-Step "Building new plugin zip (build-plugin.py)"

if (-not (Test-Path $Builder)) { throw "build-plugin.py not found at $Builder" }

$Python = Get-Command python -ErrorAction SilentlyContinue
if (-not $Python) { $Python = Get-Command py -ErrorAction SilentlyContinue }
if (-not $Python) {
    throw "Python not found on PATH. build-plugin.py is the only sanctioned way to build the zip (CLAUDE.md)."
}

# Do NOT merge stderr with 2>&1 here: under ErrorActionPreference=Stop in
# PS 5.1 that throws NativeCommandError on a perfectly good command.
Push-Location $Root
try {
    & $Python.Source $Builder
    $BuildExit = $LASTEXITCODE
}
finally { Pop-Location }

if ($BuildExit -ne 0) {
    throw "build-plugin.py exited with code $BuildExit. Zip NOT trusted - do not upload it."
}

$NewVersion = Get-ZipPluginVersion $ZipPath
Write-Ok "Built v$NewVersion -> gki-docs-helper\gki-docs-helper.zip ($(Get-KB $ZipPath) KB)"

if ($OldVersion -and ($NewVersion -eq $OldVersion)) {
    Write-Warn2 "New build has the SAME version as the archive (v$NewVersion). Browsers will serve cached CSS/JS. Bump the header and GKI_DOCS_VERSION before uploading, unless the archived v$OldVersion was never deployed."
}

Write-Host ""
Write-Host "Archive:  $ArchiveDir" -ForegroundColor DarkGray
Write-Host "Rollback: upload plugin-archive\gki-docs-helper-v<version>.zip via WP Admin > Plugins > Add New > Upload (replace)." -ForegroundColor DarkGray
