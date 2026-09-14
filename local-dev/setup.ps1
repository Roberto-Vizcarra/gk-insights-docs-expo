<#
.SYNOPSIS
    Build a local Docker WordPress from a WP Migrate export. One command,
    from downloaded archive to a verified running site.

.DESCRIPTION
    Handles the whole path:
      1.  Preflight  - Docker running, ports free, .env present, disk space
      2.  Stack up   - start MariaDB + WordPress, wait for health
      3.  Extract    - unpack the WP Migrate .zip (or accept a bare .sql/.sql.gz)
      4.  Import     - load the dump, auto-detect the table prefix
      5.  Files      - stage uploads / themes / plugins from the archive
      6.  Rewrite    - prod URL + filesystem path -> localhost (auto-detected)
      7.  Clean      - remove WP Migrate, neutralize plugins that must not run
      8.  Activate   - GKI Docs Helper, local admin, permalinks, Elementor CSS
      9.  Verify     - HTTP check, fatal scan, Elementor kit ID report

    Touches the LOCAL environment only. It cannot write to production.

.PARAMETER Archive
    The WP Migrate export: .zip, .sql, or .sql.gz.

.PARAMETER Fresh
    Destroy the existing local database and volumes first. Use this for a
    clean re-import; without it, importing over an existing site is messy.

.PARAMETER ProdPath
    Production filesystem path to rewrite (e.g. /home/site/public_html).
    Only needed if you did NOT set the path find/replace in WP Migrate.

.PARAMETER KeepMuPlugins
    By default, host-specific must-use plugins are quarantined because they
    commonly fatal outside their hosting environment. Pass this to keep them.

.EXAMPLE
    .\setup.ps1 -Archive ~\Downloads\help-gitkraken-com.zip -Fresh

.EXAMPLE
    .\setup.ps1 -Archive .\db-dump\prod.sql.gz -ProdPath /home/gitkraken/public_html
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$Archive,

    [switch]$Fresh,

    [string]$ProdUrl,
    [string]$ProdPath,

    [string]$AdminUser     = 'local',
    [string]$AdminPassword = 'local',

    [switch]$KeepMuPlugins,
    [switch]$SkipFiles,

    # Reuse the database already in the local volume instead of re-importing.
    # Use when a previous run imported successfully but failed in a later step.
    [switch]$SkipImport,

    # Delete and regenerate wp-config.php from the current compose environment.
    # The image only writes wp-config.php when it is absent, so changes to
    # WORDPRESS_* env vars are otherwise ignored on an existing volume.
    # Does not touch the database.
    [switch]$RebuildConfig
)

if ($SkipImport -and $Fresh) {
    Write-Host "  [FAIL] -SkipImport and -Fresh are contradictory: -Fresh destroys the database." -ForegroundColor Red
    exit 1
}

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

# ---------------------------------------------------------------- helpers ---

$script:StepNo = 0
function Step([string]$msg) {
    $script:StepNo++
    Write-Host ""
    Write-Host ("=== {0}/9  {1} " -f $script:StepNo, $msg).PadRight(72, '=') -ForegroundColor Cyan
}
function Ok    ([string]$m) { Write-Host "  [ok]   $m" -ForegroundColor Green }
function Warn  ([string]$m) { Write-Host "  [warn] $m" -ForegroundColor Yellow }
function Info  ([string]$m) { Write-Host "         $m" -ForegroundColor DarkGray }
function Die   ([string]$m) { Write-Host "  [FAIL] $m" -ForegroundColor Red; exit 1 }

function Get-EnvValue([string]$key, [string]$fallback) {
    if (Test-Path .\.env) {
        $line = Select-String -Path .\.env -Pattern "^\s*$key\s*=" -ErrorAction SilentlyContinue |
                Select-Object -First 1
        if ($line) { return ($line.Line -split '=', 2)[1].Trim() }
    }
    return $fallback
}

function Set-EnvValue([string]$key, [string]$value) {
    $content = Get-Content .\.env -Raw
    if ($content -match "(?m)^\s*$key\s*=") {
        $content = $content -replace "(?m)^\s*$key\s*=.*$", "$key=$value"
    } else {
        $content = $content.TrimEnd() + "`n$key=$value`n"
    }
    Set-Content .\.env -Value $content -NoNewline:$false
}

# Native-command output handling, Windows PowerShell 5.1 edition.
#
# `docker compose` writes its progress lines ("Container x  Running") to STDERR
# even on success. Merging that with 2>&1 turns them into ErrorRecords, and with
# $ErrorActionPreference='Stop' PowerShell then throws NativeCommandError on a
# perfectly successful command. So: discard stderr, judge success by exit code,
# and relax the preference around the call for good measure.

# Run wp-cli in the one-shot container. Returns trimmed stdout.
function Wp {
    $prev = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        $out = & docker compose run --rm -T wpcli @args 2>$null
        $script:WpExit = $LASTEXITCODE
        return ($out | Out-String).Trim()
    } finally { $ErrorActionPreference = $prev }
}

# Same, but discards all output - for "this plugin might not exist" calls.
function WpQuiet {
    $prev = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        & docker compose run --rm -T wpcli @args *> $null
        $script:WpExit = $LASTEXITCODE
    } finally { $ErrorActionPreference = $prev }
}

# Raw SQL against the db container. Returns tab-separated rows, no headers.
function Sql([string]$query) {
    $prev = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        $out = & docker compose exec -T db mariadb -N -B -u"$dbUser" -p"$dbPass" "$dbName" -e "$query" 2>$null
        return ($out | Out-String).Trim()
    } finally { $ErrorActionPreference = $prev }
}

# --------------------------------------------------------------- 1 preflight -

Step "Preflight"

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Die "docker not found on PATH. Start Docker Desktop and reopen this terminal."
}
docker info *> $null
if ($LASTEXITCODE -ne 0) { Die "Docker daemon is not responding. Is Docker Desktop running?" }
Ok "Docker daemon responding"

docker compose version *> $null
if ($LASTEXITCODE -ne 0) { Die "Docker Compose v2 not available (`docker compose`)." }
Ok "Compose v2 available"

if (-not (Test-Path .\.env)) {
    Copy-Item .\.env.example .\.env
    Ok "Created .env from .env.example"
}

$wpPort = Get-EnvValue 'WP_PORT'     '8080'
$dbName = Get-EnvValue 'DB_NAME'     'wordpress'
$dbUser = Get-EnvValue 'DB_USER'     'wordpress'
$dbPass = Get-EnvValue 'DB_PASSWORD' 'wordpress'
if (-not $ProdUrl) { $ProdUrl = Get-EnvValue 'PROD_URL' 'https://help.gitkraken.com' }
$localUrl = "http://localhost:$wpPort"

# Port check - but only complain if something OTHER than our own stack has it.
$portTaken = Test-NetConnection -ComputerName localhost -Port $wpPort -InformationLevel Quiet `
                -WarningAction SilentlyContinue
if ($portTaken) {
    $ours = (docker compose ps --services --filter status=running) -contains 'wordpress'
    if ($ours) {
        Info "Port $wpPort in use by this stack (will be reused)"
    } else {
        # Name the offender - guessing is worse than knowing.
        try {
            $owner = Get-NetTCPConnection -LocalPort $wpPort -State Listen -ErrorAction Stop |
                     Select-Object -First 1
            $proc  = Get-Process -Id $owner.OwningProcess -ErrorAction SilentlyContinue
            if ($proc) { Warn "Port $wpPort is held by: $($proc.ProcessName) (PID $($proc.Id))" }
        } catch { }
        Die "Port $wpPort is in use. Set a different WP_PORT in .env (try 8420, 8530, 9120)."
    }
} else {
    Ok "Port $wpPort free"
}

if (-not (Test-Path $Archive)) { Die "Archive not found: $Archive" }
$archivePath = (Resolve-Path $Archive).Path
$archiveSize = [math]::Round((Get-Item $archivePath).Length / 1MB, 1)
Ok "Archive: $(Split-Path $archivePath -Leaf)  ($archiveSize MB)"

$freeGb = [math]::Round((Get-PSDrive -Name (Get-Location).Drive.Name).Free / 1GB, 1)
if ($freeGb -lt 5) { Warn "Only $freeGb GB free on this drive. Import may fail." }
else { Info "$freeGb GB free on disk" }

# ------------------------------------------------------------- 2 stack up ----

Step "Starting the stack"

if ($Fresh) {
    Warn "-Fresh: destroying existing local database and volumes"
    docker compose down -v --remove-orphans
    Ok "Local volumes removed"
}

New-Item -ItemType Directory -Force -Path .\db-dump, .\uploads | Out-Null

docker compose up -d db wordpress
if ($LASTEXITCODE -ne 0) { Die "docker compose up failed" }

if ($RebuildConfig -and -not $Fresh) {
    Info "-RebuildConfig: regenerating wp-config.php from current env"
    docker compose exec -T -u root wordpress rm -f /var/www/html/wp-config.php 2>$null | Out-Null
    docker compose up -d --force-recreate wordpress
    Start-Sleep -Seconds 6
    Ok "wp-config.php regenerated (database untouched)"
}

Info "Waiting for MariaDB to report healthy..."
# Ask the engine directly - `compose ps --format` template support varies by version.
$deadline = (Get-Date).AddMinutes(3)
do {
    Start-Sleep -Seconds 3
    $cid = (docker compose ps -q db | Out-String).Trim()
    if ($cid) {
        $health = (docker inspect --format '{{.State.Health.Status}}' $cid 2>$null | Out-String).Trim()
    }
    if ((Get-Date) -gt $deadline) { Die "MariaDB did not become healthy in 3 minutes." }
} while ($health -ne 'healthy')
Ok "MariaDB healthy"

Info "Waiting for Apache to answer on $localUrl ..."
$deadline = (Get-Date).AddMinutes(2)
do {
    Start-Sleep -Seconds 2
    try   { $null = Invoke-WebRequest $localUrl -TimeoutSec 5 -UseBasicParsing; $up = $true }
    catch { $up = $_.Exception.Response -ne $null }   # a 302/500 still means it is listening
    if ((Get-Date) -gt $deadline) { Die "WordPress container never answered on $localUrl." }
} while (-not $up)
Ok "WordPress container responding"

# -------------------------------------------------------------- 3 extract ----

Step "Unpacking the export"

$staging = Join-Path $PSScriptRoot '.staging'
if (Test-Path $staging) { Remove-Item $staging -Recurse -Force }
New-Item -ItemType Directory -Force -Path $staging | Out-Null

$ext = [IO.Path]::GetExtension($archivePath).ToLower()
if ($ext -eq '.zip') {
    Info "Expanding zip (this can take a minute for large exports)..."
    Expand-Archive -Path $archivePath -DestinationPath $staging -Force
    Ok "Extracted to .staging"
} else {
    Copy-Item $archivePath $staging
    Info "Bare dump - no files to stage"
}

# Find the SQL dump anywhere in the extract.
$sql = Get-ChildItem $staging -Recurse -File -Include *.sql, *.sql.gz, *.gz |
       Sort-Object Length -Descending | Select-Object -First 1
if (-not $sql) { Die "No .sql or .sql.gz found inside the archive." }
Ok "Dump: $($sql.Name)  ($([math]::Round($sql.Length / 1MB, 1)) MB)"

Copy-Item $sql.FullName .\db-dump\ -Force
$dumpName = $sql.Name

# --------------------------------------------------------------- 4 import ----

Step "Importing the database"

if ($dumpName -match '\.gz$') { $reader = "gzip -dc '/dump/$dumpName'" }
else                          { $reader = "cat '/dump/$dumpName'" }

# Promote 3-byte utf8/utf8mb3 declarations to utf8mb4. Those cannot hold emoji
# or some punctuation, so those characters silently become '?' - corrupting post
# content and colliding on unique keys (SearchWP's token table surfaces it first).
#
# The match is anchored to the four places a charset is actually DECLARED, so a
# docs page that happens to discuss "utf8" in its prose is never touched. A blind
# s/utf8/utf8mb4/g would both rewrite content and turn utf8mb3 into utf8mb4mb3.
# latin1 tables are deliberately left alone.
$charsetFix = "sed -E 's/(DEFAULT CHARSET=|CHARACTER SET |SET NAMES |character_set_client = )utf8(mb3|mb4)?\b/\1utf8mb4/g; s/(COLLATE[= ])utf8(mb3|mb4)?_/\1utf8mb4_/g'"

if ($SkipImport) {
    Info "-SkipImport: reusing the database already in the local volume"
} else {
    Info "Loading $dumpName - large dumps take several minutes, no output is normal."
    Info "Normalizing charset to utf8mb4 in-stream."
    docker compose exec -T db sh -c `
        "$reader | $charsetFix | mariadb --default-character-set=utf8mb4 --max_allowed_packet=512M -u'$dbUser' -p'$dbPass' '$dbName'"
    if ($LASTEXITCODE -ne 0) {
        Write-Host ""
        Warn "The database is now in a partial state - re-run with -Fresh, not without."
        Die "Database import failed. Scroll up for the first SQL error."
    }
    Ok "Dump loaded"
}

# Auto-detect the table prefix. This is the #1 cause of "imported but WP shows
# a fresh install" - the prefix in wp-config must match the imported tables.
$optionsTable = Sql "SELECT table_name FROM information_schema.tables
                     WHERE table_schema='$dbName' AND table_name LIKE '%options'
                     ORDER BY LENGTH(table_name) ASC LIMIT 1;"
if (-not $optionsTable) { Die "No *options table in the imported database. The dump looks incomplete." }

$prefix = $optionsTable -replace 'options$', ''
$currentPrefix = Get-EnvValue 'DB_TABLE_PREFIX' 'wp_'
Ok "Detected table prefix: $prefix"

if ($prefix -ne $currentPrefix) {
    Set-EnvValue 'DB_TABLE_PREFIX' $prefix
    # The wordpress image only writes wp-config.php when it does not already
    # exist, so recreating the container would NOT pick up the new prefix.
    # Edit the live config instead.
    Wp config set table_prefix $prefix --type=variable | Out-Null
    Warn "Prefix differed from .env ($currentPrefix) - wp-config.php updated to '$prefix'"
}
$verify = (Wp config get table_prefix 2>$null)
if ($verify.Trim() -ne $prefix) { Die "wp-config table_prefix is '$verify' but the data is '$prefix'." }
Ok "wp-config.php table_prefix matches the imported data"

$postCount = Sql "SELECT COUNT(*) FROM ${prefix}posts WHERE post_status='publish';"
Ok "$postCount published posts in the database"

# Confirm the charset fix actually held. A latin1/utf8 posts table means every
# curly quote and em dash in the docs is now a '?' - fail loudly rather than
# let that masquerade as a content bug later.
$collation = Sql "SELECT table_collation FROM information_schema.tables
                  WHERE table_schema='$dbName' AND table_name='${prefix}posts';"
if ($collation -like 'utf8mb4*') {
    Ok "Posts table collation: $collation"
} else {
    Die "Posts table imported as '$collation', not utf8mb4. Content is corrupted - re-run with -Fresh."
}

# ---------------------------------------------------------------- 5 files ----

Step "Staging files"

if ($SkipFiles -or $ext -ne '.zip') {
    Info "Skipping file staging"
} else {
    # Uploads -> bind mount. You limited the export to one month, so this
    # should be a handful of YYYY/MM folders.
    $up = Get-ChildItem $staging -Recurse -Directory -Filter 'uploads' | Select-Object -First 1
    if ($up) {
        Copy-Item "$($up.FullName)\*" .\uploads\ -Recurse -Force
        $n = (Get-ChildItem .\uploads -Recurse -File).Count
        Ok "Uploads staged ($n files)"
    } else { Info "No uploads in the archive" }

    # Themes + plugins live in a named volume, so they go in via `compose cp`.
    $th = Get-ChildItem $staging -Recurse -Directory -Filter 'themes' | Select-Object -First 1
    if ($th) {
        docker compose cp "$($th.FullName)\." wordpress:/var/www/html/wp-content/themes/
        Ok "Themes staged: $((Get-ChildItem $th.FullName -Directory).Name -join ', ')"
    } else { Info "No themes in the archive" }

    $pl = Get-ChildItem $staging -Recurse -Directory -Filter 'plugins' |
          Where-Object { $_.Parent.Name -ne 'mu-plugins' } | Select-Object -First 1
    if ($pl) {
        # CRITICAL: never let prod's copy land on the bind-mounted working copy.
        $own = Join-Path $pl.FullName 'gki-docs-helper'
        if (Test-Path $own) {
            Remove-Item $own -Recurse -Force
            Ok "Removed prod's gki-docs-helper from the extract (your repo copy wins)"
        }
        docker compose cp "$($pl.FullName)\." wordpress:/var/www/html/wp-content/plugins/
        Ok "Plugins staged ($((Get-ChildItem $pl.FullName -Directory).Count) directories)"
    } else { Info "No plugins in the archive" }

    $mu = Get-ChildItem $staging -Recurse -Directory -Filter 'mu-plugins' | Select-Object -First 1
    if ($mu) {
        if ($KeepMuPlugins) {
            docker compose cp "$($mu.FullName)\." wordpress:/var/www/html/wp-content/mu-plugins/
            Warn "Must-use plugins staged. If the site white-screens, delete them:"
            Info "  docker compose exec wordpress rm -rf /var/www/html/wp-content/mu-plugins"
        } else {
            Info "Skipping must-use plugins (host-specific ones fatal locally)."
            Info "Pass -KeepMuPlugins if you need them."
        }
    }

    # The container runs as www-data; copied files arrive root-owned.
    docker compose exec -T -u root wordpress chown -R www-data:www-data `
        /var/www/html/wp-content/themes /var/www/html/wp-content/plugins 2>$null | Out-Null
    Ok "Ownership fixed"
}

# -------------------------------------------------------------- 6 rewrite ----

Step "Rewriting production URLs and paths"

$dbSiteUrl = Sql "SELECT option_value FROM ${prefix}options WHERE option_name='siteurl' LIMIT 1;"
Info "siteurl in the dump: $dbSiteUrl"

if ($dbSiteUrl -eq $localUrl) {
    Ok "WP Migrate already rewrote URLs at export - nothing to do"
} elseif ($dbSiteUrl -like '*localhost*') {
    # WP Migrate rewrote to localhost, but on a different port than this stack
    # is serving (usually because WP_PORT changed after a port conflict).
    Warn "Dump points at $dbSiteUrl but this stack serves $localUrl"
    Info "Rewriting the port mismatch..."
    Wp search-replace "$dbSiteUrl" "$localUrl" --all-tables --precise --skip-columns=guid --report-changed-only
    Ok "Port corrected to $localUrl"
} else {
    $detected = $dbSiteUrl
    if (-not $detected) { $detected = $ProdUrl }
    $detectedHost = ([uri]$detected).Host

    Info "Rewriting $detected -> $localUrl"
    # --precise walks serialized data properly. A plain text replace corrupts
    # PHP serialized string lengths, which is how Elementor layouts silently die.
    Wp search-replace "$detected" "$localUrl" --all-tables --precise --skip-columns=guid --report-changed-only
    Wp search-replace "//$detectedHost" "//localhost:$wpPort" --all-tables --precise --skip-columns=guid --report-changed-only
    Ok "URLs rewritten"
}

if ($ProdPath) {
    Info "Rewriting filesystem path $ProdPath -> /var/www/html"
    Wp search-replace "$ProdPath" "/var/www/html" --all-tables --precise --report-changed-only
    Ok "Paths rewritten"
} else {
    Info "No -ProdPath given. If Elementor styles look wrong, re-run with the"
    Info "prod path (Site Health -> Info -> Server -> document root)."
}

Wp option update home    "$localUrl" | Out-Null
Wp option update siteurl "$localUrl" | Out-Null
Ok "home / siteurl pinned to $localUrl"

# ---------------------------------------------------------------- 7 clean ----

Step "Cleaning up"

# WP Migrate has done its job and can push to remote sites. Remove it entirely
# so a stray click in the local admin can never reach production.
foreach ($slug in @('wp-migrate-db', 'wp-migrate-db-pro', 'wp-migrate')) {
    WpQuiet plugin deactivate $slug
    WpQuiet plugin delete $slug
}
Ok "WP Migrate removed from the local site"

# OAuth cannot round-trip to localhost; caching/CDN/security plugins mask the
# exact CSS and template bugs this environment exists to reproduce.
$deactivate = @(
    'daggerhart-openid-connect-generic',
    'git-it-write',
    'wp-rocket', 'w3-total-cache', 'wp-super-cache', 'litespeed-cache', 'autoptimize',
    'wordfence', 'sucuri-scanner', 'jetpack', 'cloudflare',
    'updraftplus', 'wpvivid-backuprestore', 'duplicator'
)
$off = @()
foreach ($slug in $deactivate) {
    $state = (Wp plugin get $slug --field=status 2>$null)
    if ($state -eq 'active') { WpQuiet plugin deactivate $slug; $off += $slug }
}
if ($off) { Ok "Deactivated: $($off -join ', ')" } else { Info "Nothing needed deactivating" }

WpQuiet option update gki_auth_gate_enabled 0
Ok "Auth gate forced off (cannot complete OAuth against localhost)"

# Stop the local copy emailing anyone or hitting prod search indexes.
WpQuiet option update blog_public 0
WpQuiet option update blogname "[LOCAL] GKI Docs"
Ok "Marked as a local, non-indexed site"

# ------------------------------------------------------------- 8 activate ----

Step "Activating and configuring"

Wp plugin activate gki-docs-helper | Out-Null
Ok "GKI Docs Helper activated"

$existing = (Wp user get $AdminUser --field=ID 2>$null)
if ($existing -match '^\d+$') {
    Info "Admin '$AdminUser' already exists"
} else {
    WpQuiet user create $AdminUser "$AdminUser@example.test" --role=administrator --user_pass=$AdminPassword
    Ok "Admin created: $AdminUser / $AdminPassword"
}

# Do NOT set the permalink structure here. Production's structure is part of
# what we imported, and /insights-expo/{slug} depends on it. Overwriting it
# with a guess silently changes every URL on the site. Flush only.
$structure = Wp option get permalink_structure
if ($structure) { Ok "Permalink structure (from prod): $structure" }
else            { Warn "Permalink structure is 'plain' - URLs will not match production" }
Wp rewrite flush --hard | Out-Null
Ok "Rewrite rules flushed"

# Elementor caches generated CSS keyed to the old domain and paths.
WpQuiet elementor flush-css
Ok "Elementor CSS cache flushed"

# --------------------------------------------------------------- 9 verify ----

Step "Verifying"

$activeKit = Sql "SELECT option_value FROM ${prefix}options WHERE option_name='elementor_active_kit' LIMIT 1;"
if ($activeKit) {
    if ($activeKit -eq '5') {
        Ok "Elementor active kit ID = 5  -> .elementor-kit-5 matches production"
    } else {
        Warn "Elementor active kit ID = $activeKit  -> selector is .elementor-kit-$activeKit locally"
        Info "Production overrides target .elementor-kit-5. Your CSS will not apply"
        Info "locally until these match. Fix: in the local admin, or re-import"
        Info "keeping post IDs intact (a full SQL dump preserves them)."
    }
} else {
    Warn "No elementor_active_kit option found - Elementor may not be active"
}

$gkiPosts = Sql "SELECT COUNT(*) FROM ${prefix}posts WHERE post_name LIKE 'expo-ai-adoption%' AND post_status='publish';"
if ([int]$gkiPosts -gt 0) { Ok "$gkiPosts insights-expo pages present" }
else { Warn "No expo-ai-adoption-* posts found. Did the export include all post types?" }

# Ask WordPress what the URL actually is rather than assuming it. This also
# reveals whether the docs are regular posts or a custom post type, which is
# what puts the /insights-expo/ prefix on the path.
$testUrl = "$localUrl/insights-expo/expo-ai-adoption-home"
$homeId  = Sql "SELECT ID FROM ${prefix}posts
                WHERE post_name='expo-ai-adoption-home' AND post_status='publish' LIMIT 1;"
if ($homeId -match '^\d+$') {
    $ptype     = Sql "SELECT post_type FROM ${prefix}posts WHERE ID=$homeId;"
    $permalink = Wp eval "echo get_permalink($homeId);"
    Info "Home page: ID $homeId, post_type '$ptype'"
    if ($permalink -like 'http*') {
        Ok "WordPress reports permalink: $permalink"
        $testUrl = $permalink
    }
}

try {
    $resp = Invoke-WebRequest $testUrl -TimeoutSec 20 -UseBasicParsing
    Ok "$testUrl -> HTTP $($resp.StatusCode) ($([math]::Round($resp.RawContentLength/1KB,1)) KB)"
    if ($resp.Content -match 'gki-layout')       { Ok "GKI template rendered (.gki-layout present)" }
    else                                          { Warn "Page loaded but .gki-layout is missing - template not applied" }
    if ($resp.Content -match 'elementor-kit-(\d+)') { Info "Body carries .elementor-kit-$($Matches[1])" }
} catch {
    Warn "$testUrl -> $($_.Exception.Message)"
}

# grep -c prints "0" AND exits 1 when there are no matches, so a naive
# `|| echo 0` emits "0\n0". Branch on the file existing instead, and swallow
# grep's exit code without adding a second line.
$raw = docker compose exec -T wordpress sh -c `
    "if [ -f /var/www/html/wp-content/debug.log ]; then grep -c 'PHP Fatal' /var/www/html/wp-content/debug.log || true; else echo 0; fi" 2>$null
$fatals = ($raw -split "`r?`n" | Where-Object { $_ -match '^\s*\d+\s*$' } | Select-Object -First 1)
if (-not $fatals) { $fatals = '0' }
$fatals = $fatals.Trim()

if ([int]$fatals -gt 0) {
    Warn "$fatals PHP fatal(s) in debug.log:"
    docker compose exec -T wordpress sh -c "grep 'PHP Fatal' /var/www/html/wp-content/debug.log | tail -5"
} else {
    Ok "No PHP fatals in debug.log"
}

Remove-Item $staging -Recurse -Force -ErrorAction SilentlyContinue

Write-Host ""
Write-Host ("=" * 72) -ForegroundColor Green
Write-Host " Local site is up." -ForegroundColor Green
Write-Host ("=" * 72) -ForegroundColor Green
Write-Host "  Docs     $testUrl"
Write-Host "  Admin    $localUrl/wp-admin   ($AdminUser / $AdminPassword)"
Write-Host "  DB tool  docker compose --profile tools up -d adminer  -> http://localhost:$(Get-EnvValue 'ADMINER_PORT' '8081')"
Write-Host "  Log      docker compose exec wordpress tail -f /var/www/html/wp-content/debug.log"
Write-Host "  Stop     docker compose down        (keeps the database)"
Write-Host "  Reset    docker compose down -v     (destroys it)"
Write-Host ""
Write-Host "  gki-docs-helper/ is bind-mounted - edit CSS/PHP and refresh." -ForegroundColor DarkGray
Write-Host "  Bump GKI_DOCS_VERSION when testing CSS/JS or the browser serves cache." -ForegroundColor DarkGray
Write-Host ""
