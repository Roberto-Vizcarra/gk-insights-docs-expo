# DEPRECATED - superseded by setup.ps1
#
# setup.ps1 does everything this did, plus preflight checks, zip extraction,
# theme/plugin staging, table-prefix auto-detection, WP Migrate removal, and
# post-import verification.
#
#   .\setup.ps1 -Archive ~\Downloads\<export>.zip -Fresh
#
# Safe to delete this file.

Write-Host "import.ps1 is deprecated. Use setup.ps1 instead:" -ForegroundColor Yellow
Write-Host "  .\setup.ps1 -Archive <path-to-export.zip> -Fresh" -ForegroundColor Yellow
