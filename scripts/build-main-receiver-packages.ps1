Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = Split-Path -Parent $PSScriptRoot
$distDir = Join-Path $repoRoot 'dist'
$installDir = Join-Path $repoRoot 'Plugin Installation Files'
$tempDir = Join-Path $repoRoot '.build-temp-release-only'

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Get-PluginVersion {
    param(
        [Parameter(Mandatory = $true)]
        [string] $PluginFile
    )

    $content = [System.IO.File]::ReadAllText($PluginFile)
    $match = [regex]::Match($content, '^[ \t]*\*[ \t]*Version:[ \t]*(.+)$', [System.Text.RegularExpressions.RegexOptions]::Multiline)
    if (-not $match.Success) {
        throw "Could not read plugin version from $PluginFile"
    }

    return $match.Groups[1].Value.Trim()
}

function Get-RelativePathForZip {
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

    return [System.Uri]::UnescapeDataString($baseUri.MakeRelativeUri($targetUri).ToString()).Replace('/', '/')
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

    $sourceRoot = (Resolve-Path -LiteralPath $SourceDir).Path
    $archive = [System.IO.Compression.ZipFile]::Open($ZipPath, [System.IO.Compression.ZipArchiveMode]::Create)

    try {
        $items = Get-ChildItem -LiteralPath $sourceRoot -Recurse -Force

        foreach ($directory in ($items | Where-Object { $_.PSIsContainer })) {
            $relativePath = Get-RelativePathForZip -BaseDir $sourceRoot -TargetPath $directory.FullName
            if ($relativePath -ne '.') {
                $null = $archive.CreateEntry($relativePath + '/')
            }
        }

        foreach ($file in ($items | Where-Object { -not $_.PSIsContainer })) {
            $relativePath = Get-RelativePathForZip -BaseDir $sourceRoot -TargetPath $file.FullName
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                $archive,
                $file.FullName,
                $relativePath,
                [System.IO.Compression.CompressionLevel]::Optimal
            ) | Out-Null
        }
    }
    finally {
        $archive.Dispose()
    }
}

if (Test-Path -LiteralPath $tempDir) {
    Remove-Item -LiteralPath $tempDir -Recurse -Force
}

New-Item -ItemType Directory -Path $tempDir | Out-Null
New-Item -ItemType Directory -Path $distDir -Force | Out-Null
New-Item -ItemType Directory -Path $installDir -Force | Out-Null

$targets = @(
    @{
        Name = 'wine-e-label'
        SourceDir = Join-Path $repoRoot 'Wine-E-Label-v2.3.1'
        EntryFile = Join-Path $repoRoot 'Wine-E-Label-v2.3.1\wine-e-label.php'
    },
    @{
        Name = 'wine-e-label-receiver'
        SourceDir = Join-Path $repoRoot 'Wine-E-Label-Receiver-v2.3.1\wine-e-label-receiver'
        EntryFile = Join-Path $repoRoot 'Wine-E-Label-Receiver-v2.3.1\wine-e-label-receiver\wine-e-label-receiver.php'
    }
)

foreach ($target in $targets) {
    $version = Get-PluginVersion -PluginFile $target.EntryFile
    $packageRoot = Join-Path $tempDir ($target.Name + '-package')
    $stageRoot = Join-Path $packageRoot $target.Name

    New-Item -ItemType Directory -Path $stageRoot -Force | Out-Null
    Copy-Item -Path (Join-Path $target.SourceDir '*') -Destination $stageRoot -Recurse -Force

    New-ZipFromDirectory -SourceDir $packageRoot -ZipPath (Join-Path $distDir ($target.Name + '-' + $version + '.zip'))
    New-ZipFromDirectory -SourceDir $stageRoot -ZipPath (Join-Path $installDir ($target.Name + '.zip'))

    Write-Host ('Built package for {0} {1}' -f $target.Name, $version)
}

Remove-Item -LiteralPath $tempDir -Recurse -Force
