param(
    [switch]$DryRun,
    [switch]$Force
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$currentBranch = git branch --show-current
if (-not $Force -and $currentBranch -notlike 'release/to-master*') {
    Write-Error "Current branch is '$currentBranch'. Switch to a 'release/to-master*' branch or use -Force."
    exit 1
}

$staticTargets = @(
    'docker-compose.yml',
    'prometheus.yml',
    'grafana-dashboard.yaml',
    'grafana-datasource.yaml',
    'dashboards',
    'scripts/smoke.js',
    'error_log',
    'config/error_log',
    'public/error_log',
    'images.lnk',
    'R-SMarketPlace-Home-Electronics-Gadgets-12-11-2025_01_22_PM.png',
    '.ftp-deploy-sync-state.json',
    '.phpunit.result.cache'
)

$rootMarkdownTargets = Get-ChildItem -Path . -Filter '*.md' -File |
    Where-Object { $_.Name -ne 'README.md' } |
    ForEach-Object { $_.Name }

$targets = @($rootMarkdownTargets + $staticTargets | Select-Object -Unique)

$removed = @()
$missing = @()

foreach ($target in $targets) {
    if (Test-Path -LiteralPath $target) {
        if ($DryRun) {
            $removed += "$target (would remove)"
        } else {
            Remove-Item -LiteralPath $target -Recurse -Force
            $removed += $target
        }
    } else {
        $missing += $target
    }
}

Write-Host 'Removed:'
if ($removed.Count -gt 0) {
    $removed | ForEach-Object { Write-Host "  $_" }
} else {
    Write-Host '  None'
}

Write-Host ''
Write-Host 'Missing:'
if ($missing.Count -gt 0) {
    $missing | ForEach-Object { Write-Host "  $_" }
} else {
    Write-Host '  None'
}

Write-Host ''
if ($DryRun) {
    Write-Host 'Dry run only. No files were deleted.'
} else {
    Write-Host 'Cleanup complete. Review with: git status --short'
}
