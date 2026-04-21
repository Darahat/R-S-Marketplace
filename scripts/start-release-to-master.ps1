param(
    [string]$FromBranch = 'development',
    [string]$DateTag = (Get-Date -Format 'yyyy-MM-dd'),
    [switch]$DryRun,
    [switch]$SkipCommit
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Run-Git([string]$Command) {
    Write-Host "> git $Command"
    $result = git $Command.Split(' ')
    return $result
}

# Guard: keep workflow deterministic and avoid carrying accidental local edits.
$status = git status --porcelain
if ($status) {
    Write-Error 'Working tree is not clean. Commit/stash changes first, then rerun.'
    exit 1
}

$releaseBranch = "release/to-master-$DateTag"

$current = (git branch --show-current).ToString().Trim()
if ($current -ne $FromBranch) {
    Run-Git "checkout $FromBranch" | Out-Null
}

$exists = [string](git branch --list $releaseBranch).ToString().Trim()
if ($exists) {
    Write-Error "Branch '$releaseBranch' already exists. Use a different -DateTag."
    exit 1
}

Run-Git "checkout -b $releaseBranch" | Out-Null

if ($DryRun) {
    powershell -ExecutionPolicy Bypass -File scripts/prepare-master-release.ps1 -DryRun
    Write-Host ''
    Write-Host 'Dry run complete. No files were deleted and nothing was committed.'
    exit 0
}

powershell -ExecutionPolicy Bypass -File scripts/prepare-master-release.ps1

$changes = git status --porcelain
if (-not $changes) {
    Write-Host 'No cleanup changes detected. Nothing to commit.'
    exit 0
}

if (-not $SkipCommit) {
    git add -A
    git commit -m "Release prep: remove dev-only files for master"
    Write-Host ''
    Write-Host "Release branch prepared and committed: $releaseBranch"
} else {
    Write-Host ''
    Write-Host "Release branch prepared (not committed): $releaseBranch"
}

Write-Host ''
Write-Host 'Next steps:'
Write-Host '  git checkout master'
Write-Host "  git merge --no-ff $releaseBranch"
Write-Host '  # run final checks/tests'
Write-Host '  git checkout main'
Write-Host '  git merge --no-ff master'
