Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = Split-Path -Parent $PSScriptRoot
$distDir = Join-Path $repoRoot 'dist'
$installDir = Join-Path $repoRoot 'Plugin Installation Files'
$updatesDir = Join-Path $repoRoot 'updates'
$tempDir = Join-Path $repoRoot '.build-temp'

$mainSourceDir = Join-Path $repoRoot 'Wine-E-Label-v2.3.1'
$mainEntryFile = Join-Path $mainSourceDir 'wine-e-label.php'
$receiverSourceDir = Join-Path $repoRoot 'Wine-E-Label-Receiver-v2.3.1\wine-e-label-receiver'
$receiverEntryFile = Join-Path $receiverSourceDir 'wine-e-label-receiver.php'

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

function Get-PluginHeaderValue {
    param(
        [Parameter(Mandatory = $true)]
        [string] $PluginFile,
        [Parameter(Mandatory = $true)]
        [string] $HeaderName
    )

    $content = Get-Content -LiteralPath $PluginFile -Raw
    $escapedHeader = [regex]::Escape($HeaderName)
    $match = [regex]::Match($content, "^[ \t]*\*[ \t]*${escapedHeader}:[ \t]*(.+)$", [System.Text.RegularExpressions.RegexOptions]::Multiline)
    if (-not $match.Success) {
        return ''
    }

    return $match.Groups[1].Value.Trim()
}

function Get-RelativeZipPath {
    param(
        [Parameter(Mandatory = $true)]
        [string] $BaseDir,
        [Parameter(Mandatory = $true)]
        [string] $TargetPath
    )

    $basePath = [System.IO.Path]::GetFullPath($BaseDir)
    if (-not $basePath.EndsWith([System.IO.Path]::DirectorySeparatorChar.ToString())) {
        $basePath += [System.IO.Path]::DirectorySeparatorChar
    }

    $baseUri = New-Object System.Uri($basePath)
    $targetUri = New-Object System.Uri([System.IO.Path]::GetFullPath($TargetPath))

    return $baseUri.MakeRelativeUri($targetUri).ToString()
}

function New-ZipFromDirectory {
    param(
        [Parameter(Mandatory = $true)]
        [string] $SourceDir,
        [Parameter(Mandatory = $true)]
        [string] $ZipPath
    )

    if (Test-Path -LiteralPath $ZipPath) {
        Remove-Item -LiteralPath $ZipPath -Force
    }

    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem

    $sourceRoot = (Resolve-Path -LiteralPath $SourceDir).Path
    $zipArchive = [System.IO.Compression.ZipFile]::Open($ZipPath, [System.IO.Compression.ZipArchiveMode]::Create)

    try {
        $allItems = Get-ChildItem -LiteralPath $sourceRoot -Recurse -Force

        foreach ($directory in ($allItems | Where-Object { $_.PSIsContainer })) {
            $relativePath = Get-RelativeZipPath -BaseDir $sourceRoot -TargetPath $directory.FullName
            $entryName = ($relativePath -replace '\\', '/') + '/'
            $null = $zipArchive.CreateEntry($entryName)
        }

        foreach ($file in ($allItems | Where-Object { -not $_.PSIsContainer })) {
            $relativePath = Get-RelativeZipPath -BaseDir $sourceRoot -TargetPath $file.FullName
            $entryName = $relativePath -replace '\\', '/'
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                $zipArchive,
                $file.FullName,
                $entryName,
                [System.IO.Compression.CompressionLevel]::Optimal
            ) | Out-Null
        }
    }
    finally {
        $zipArchive.Dispose()
    }
}

if (Test-Path -LiteralPath $tempDir) {
    Remove-Item -LiteralPath $tempDir -Recurse -Force
}

New-Item -ItemType Directory -Path $tempDir | Out-Null
New-Item -ItemType Directory -Path $distDir -Force | Out-Null
New-Item -ItemType Directory -Path $installDir -Force | Out-Null
New-Item -ItemType Directory -Path $updatesDir -Force | Out-Null

$mainPackageRoot = Join-Path $tempDir 'main-package'
$receiverPackageRoot = Join-Path $tempDir 'receiver-package'
$mainStageRoot = Join-Path $mainPackageRoot 'wine-e-label'
$receiverStageRoot = Join-Path $receiverPackageRoot 'wine-e-label-receiver'

New-Item -ItemType Directory -Path $mainPackageRoot | Out-Null
New-Item -ItemType Directory -Path $receiverPackageRoot | Out-Null
New-Item -ItemType Directory -Path $mainStageRoot | Out-Null
New-Item -ItemType Directory -Path $receiverStageRoot | Out-Null

Copy-Item -Path (Join-Path $mainSourceDir '*') -Destination $mainStageRoot -Recurse -Force
Copy-Item -Path (Join-Path $receiverSourceDir '*') -Destination $receiverStageRoot -Recurse -Force

$mainVersion = Get-PluginVersion -PluginFile $mainEntryFile
$receiverVersion = Get-PluginVersion -PluginFile $receiverEntryFile
$mainName = Get-PluginHeaderValue -PluginFile $mainEntryFile -HeaderName 'Plugin Name'
$receiverName = Get-PluginHeaderValue -PluginFile $receiverEntryFile -HeaderName 'Plugin Name'
$mainRequires = Get-PluginHeaderValue -PluginFile $mainEntryFile -HeaderName 'Requires at least'
$receiverRequires = Get-PluginHeaderValue -PluginFile $receiverEntryFile -HeaderName 'Requires at least'
$mainRequiresPhp = Get-PluginHeaderValue -PluginFile $mainEntryFile -HeaderName 'Requires PHP'
$receiverRequiresPhp = Get-PluginHeaderValue -PluginFile $receiverEntryFile -HeaderName 'Requires PHP'

$mainZip = Join-Path $distDir ("wine-e-label-$mainVersion.zip")
$receiverZip = Join-Path $distDir ("wine-e-label-receiver-$receiverVersion.zip")
$mainStableZip = Join-Path $installDir 'wine-e-label.zip'
$receiverStableZip = Join-Path $installDir 'wine-e-label-receiver.zip'
$manifestFile = Join-Path $updatesDir 'plugin-updates.json'

# Keep the manual-install folder clean so it only contains the current
# stable plugin ZIPs instead of old fix, overwrite, backup, or legacy packages.
Get-ChildItem -LiteralPath $installDir -File |
    Where-Object {
        $_.Name -like 'wine-e-label*.zip*' -or
        $_.Name -like 'reith-naehrwert-html-importer*.zip*'
    } |
    Remove-Item -Force

New-ZipFromDirectory -SourceDir $mainPackageRoot -ZipPath $mainZip
New-ZipFromDirectory -SourceDir $receiverPackageRoot -ZipPath $receiverZip
# The install packages are built flat so WordPress can install them without
# creating an extra wrapper directory like wine-e-label-1/wine-e-label/.
New-ZipFromDirectory -SourceDir $mainStageRoot -ZipPath $mainStableZip
New-ZipFromDirectory -SourceDir $receiverStageRoot -ZipPath $receiverStableZip

$generatedAtUtc = [DateTime]::UtcNow.ToString('yyyy-MM-ddTHH:mm:ssZ')
$lastUpdated = [DateTime]::Now.ToString('yyyy-MM-dd HH:mm:ss')
$repoUrl = 'https://github.com/jo-286/wine-e-label'
$rawBase = 'https://raw.githubusercontent.com/jo-286/wine-e-label/main'
$installWebPath = 'Plugin%20Installation%20Files'

$manifest = [ordered]@{
    manifest_version = 1
    repository = $repoUrl
    generated_at = $generatedAtUtc
    plugins = [ordered]@{
        'wine-e-label' = [ordered]@{
            name = $mainName
            slug = 'wine-e-label'
            version = $mainVersion
            homepage = $repoUrl
            package = "$rawBase/$installWebPath/wine-e-label.zip?ver=$mainVersion"
            requires = $mainRequires
            requires_php = $mainRequiresPhp
            last_updated = $lastUpdated
            author = 'Johannes Reith, Markus Hammer'
            author_profile = 'https://github.com/jo-286'
            sections = [ordered]@{
                description = 'Wein E-Label is the main WordPress and WooCommerce plugin for wine e-label workflows, including manual data entry, imports, QR code generation, multilingual label pages, and optional receiver delivery.'
                installation = 'Install or update with the packaged wine-e-label.zip file. Future versions can then be updated directly from the shared GitHub repository through the WordPress plugin updater.'
                changelog = "Current packaged version: $mainVersion`nShared repository: $repoUrl`nStable package: Plugin Installation Files/wine-e-label.zip"
            }
        }
        'wine-e-label-receiver' = [ordered]@{
            name = $receiverName
            slug = 'wine-e-label-receiver'
            version = $receiverVersion
            homepage = $repoUrl
            package = "$rawBase/$installWebPath/wine-e-label-receiver.zip?ver=$receiverVersion"
            requires = $receiverRequires
            requires_php = $receiverRequiresPhp
            last_updated = $lastUpdated
            author = 'Johannes Reith'
            author_profile = 'https://github.com/jo-286'
            sections = [ordered]@{
                description = 'Wein E-Label Receiver is the companion plugin for a separate WordPress site that receives and serves wine e-label pages over REST without theme layout.'
                installation = 'Install or update with the packaged wine-e-label-receiver.zip file. Future versions can then be updated directly from the shared GitHub repository through the WordPress plugin updater.'
                changelog = "Current packaged version: $receiverVersion`nShared repository: $repoUrl`nStable package: Plugin Installation Files/wine-e-label-receiver.zip"
            }
        }
    }
}

$manifestJson = ConvertTo-Json -InputObject $manifest -Depth 10
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($manifestFile, $manifestJson, $utf8NoBom)

Write-Host "Created package: $mainZip"
Write-Host "Created package: $receiverZip"
Write-Host "Created package: $mainStableZip"
Write-Host "Created package: $receiverStableZip"
Write-Host "Created update manifest: $manifestFile"
