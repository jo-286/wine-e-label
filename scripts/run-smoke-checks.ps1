param(
    [string] $PhpExecutable = '',
    [switch] $SkipPhpLint
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = Split-Path -Parent $PSScriptRoot
$mainPluginRoot = Join-Path $repoRoot 'Wine-E-Label-v2.3.1'
$receiverPluginRoot = Join-Path $repoRoot 'Wine-E-Label-Receiver-v2.3.1\wine-e-label-receiver'

function Get-PhpExecutablePath {
    param(
        [string] $ExplicitPath
    )

    if ($ExplicitPath -ne '') {
        return $ExplicitPath
    }

    if ($env:PHP_BINARY) {
        return $env:PHP_BINARY
    }

    if ($env:PHP_PATH) {
        return $env:PHP_PATH
    }

    $command = Get-Command php -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    return ''
}

function Invoke-PhpLint {
    param(
        [Parameter(Mandatory = $true)]
        [string] $RootPath,
        [Parameter(Mandatory = $true)]
        [string] $Label,
        [Parameter(Mandatory = $true)]
        [string] $PhpPath
    )

    $phpFiles = Get-ChildItem -LiteralPath $RootPath -Recurse -File -Filter '*.php' |
        Where-Object { $_.FullName -notmatch '\\vendor\\' }

    foreach ($file in $phpFiles) {
        Write-Host ("Linting {0}: {1}" -f $Label, $file.FullName)
        & $PhpPath -l $file.FullName | Out-Host
        if ($LASTEXITCODE -ne 0) {
            throw "PHP lint failed for $($file.FullName)"
        }
    }
}

function Assert-FileExists {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Path,
        [Parameter(Mandatory = $true)]
        [string] $Description
    )

    if (-not (Test-Path -LiteralPath $Path)) {
        throw "Missing $Description at $Path"
    }
}

function Get-PluginVersion {
    param(
        [Parameter(Mandatory = $true)]
        [string] $PluginFile
    )

    $content = Get-Content -LiteralPath $PluginFile -Raw
    $match = [regex]::Match($content, '^[ \t]*\*[ \t]*Version:[ \t]*(.+)$', [System.Text.RegularExpressions.RegexOptions]::Multiline)
    if (-not $match.Success) {
        throw "Could not read plugin version from $PluginFile"
    }

    return $match.Groups[1].Value.Trim()
}

Assert-FileExists -Path (Join-Path $mainPluginRoot 'wine-e-label.php') -Description 'main plugin entry file'
Assert-FileExists -Path (Join-Path $receiverPluginRoot 'wine-e-label-receiver.php') -Description 'receiver plugin entry file'
Assert-FileExists -Path (Join-Path $mainPluginRoot 'languages\wine-e-label.pot') -Description 'main plugin translation template'
Assert-FileExists -Path (Join-Path $receiverPluginRoot 'languages\wine-e-label-receiver.pot') -Description 'receiver translation template'
Assert-FileExists -Path (Join-Path $mainPluginRoot 'languages\wine-e-label-de_DE.po') -Description 'main plugin German translation file'
Assert-FileExists -Path (Join-Path $mainPluginRoot 'languages\wine-e-label-de_DE.mo') -Description 'main plugin German compiled translation file'
Assert-FileExists -Path (Join-Path $mainPluginRoot 'languages\wine-e-label-de_DE_formal.po') -Description 'main plugin German formal translation file'
Assert-FileExists -Path (Join-Path $mainPluginRoot 'languages\wine-e-label-en_US.po') -Description 'main plugin English translation file'
Assert-FileExists -Path (Join-Path $mainPluginRoot 'languages\wine-e-label-en_US.mo') -Description 'main plugin English compiled translation file'
Assert-FileExists -Path (Join-Path $receiverPluginRoot 'languages\wine-e-label-receiver-de_DE.po') -Description 'receiver German translation file'
Assert-FileExists -Path (Join-Path $receiverPluginRoot 'languages\wine-e-label-receiver-de_DE.mo') -Description 'receiver German compiled translation file'
Assert-FileExists -Path (Join-Path $receiverPluginRoot 'languages\wine-e-label-receiver-de_DE_formal.po') -Description 'receiver German formal translation file'
Assert-FileExists -Path (Join-Path $receiverPluginRoot 'languages\wine-e-label-receiver-en_US.po') -Description 'receiver English translation file'
Assert-FileExists -Path (Join-Path $receiverPluginRoot 'languages\wine-e-label-receiver-en_US.mo') -Description 'receiver English compiled translation file'

$mainVersion = Get-PluginVersion -PluginFile (Join-Path $mainPluginRoot 'wine-e-label.php')
$receiverVersion = Get-PluginVersion -PluginFile (Join-Path $receiverPluginRoot 'wine-e-label-receiver.php')

if ([string]::IsNullOrWhiteSpace($mainVersion) -or [string]::IsNullOrWhiteSpace($receiverVersion)) {
    throw 'Plugin version lookup returned an empty value.'
}

$phpPath = Get-PhpExecutablePath -ExplicitPath $PhpExecutable
if ($SkipPhpLint) {
    Write-Host 'Skipping PHP lint because -SkipPhpLint was provided.'
} elseif ($phpPath -eq '') {
    throw 'No PHP executable found. Pass -PhpExecutable or set PHP_BINARY/PHP_PATH.'
} else {
    Invoke-PhpLint -RootPath $mainPluginRoot -Label 'main plugin' -PhpPath $phpPath
    Invoke-PhpLint -RootPath $receiverPluginRoot -Label 'receiver plugin' -PhpPath $phpPath
}

Write-Host ("Smoke checks passed for Wein E-Label {0} and Wein E-Label Receiver {1}." -f $mainVersion, $receiverVersion)
