Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

$repoRoot = Split-Path -Parent $PSScriptRoot

$templateTargets = @(
    @{
        Root = Join-Path $repoRoot 'Wine-E-Label-v2.3.1'
        Domain = 'wine-e-label'
        Output = Join-Path $repoRoot 'Wine-E-Label-v2.3.1\languages\wine-e-label.pot'
        Package = 'Wein E-Label'
    },
    @{
        Root = Join-Path $repoRoot 'Wine-E-Label-Receiver-v2.3.1\wine-e-label-receiver'
        Domain = 'wine-e-label-receiver'
        Output = Join-Path $repoRoot 'Wine-E-Label-Receiver-v2.3.1\wine-e-label-receiver\languages\wine-e-label-receiver.pot'
        Package = 'Wein E-Label Receiver'
    }
)

function Convert-PHPStringLiteral {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Value
    )

    $converted = $Value
    $converted = $converted -replace "\\\\", "\"
    $converted = $converted -replace "\\'", "'"
    $converted = $converted -replace '\\"', '"'
    $converted = $converted -replace "\\r", "`r"
    $converted = $converted -replace "\\n", "`n"
    $converted = $converted -replace "\\t", "`t"
    return $converted
}

function Get-LineNumber {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Content,
        [Parameter(Mandatory = $true)]
        [int] $Index
    )

    if ($Index -le 0) {
        return 1
    }

    return ([regex]::Matches($Content.Substring(0, $Index), "`n")).Count + 1
}

function Add-EntryReference {
    param(
        [Parameter(Mandatory = $true)]
        [hashtable] $Entry,
        [Parameter(Mandatory = $true)]
        [string] $Reference
    )

    if (-not $Entry.References.Contains($Reference)) {
        [void] $Entry.References.Add($Reference)
    }
}

function New-EntryKey {
    param(
        [string] $Context,
        [string] $Singular,
        [string] $Plural
    )

    return '{0}|{1}|{2}' -f $Context, $Singular, $Plural
}

function Escape-PoString {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Value
    )

    $escaped = $Value.Replace('\', '\\').Replace('"', '\"')
    $escaped = $escaped.Replace("`r", '\r').Replace("`n", '\n')
    return $escaped
}

function Get-RelativePath {
    param(
        [Parameter(Mandatory = $true)]
        [string] $BasePath,
        [Parameter(Mandatory = $true)]
        [string] $TargetPath
    )

    $baseFullPath = [System.IO.Path]::GetFullPath($BasePath)
    if (-not $baseFullPath.EndsWith([System.IO.Path]::DirectorySeparatorChar.ToString())) {
        $baseFullPath += [System.IO.Path]::DirectorySeparatorChar
    }

    $baseUri = New-Object System.Uri($baseFullPath)
    $targetUri = New-Object System.Uri([System.IO.Path]::GetFullPath($TargetPath))
    return [System.Uri]::UnescapeDataString($baseUri.MakeRelativeUri($targetUri).ToString()).Replace('/', '\')
}

function New-EntryMap {
    return @{}
}

function Add-SimpleMatches {
    param(
        [Parameter(Mandatory = $true)]
        [hashtable] $Entries,
        [Parameter(Mandatory = $true)]
        [string] $Content,
        [Parameter(Mandatory = $true)]
        [string] $RelativePath,
        [Parameter(Mandatory = $true)]
        [string] $Domain
    )

    $pattern = "(?s)(?<func>__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*(?<msgQuote>'|"")(?<msg>(?:\\.|(?!\k<msgQuote>).)*)\k<msgQuote>\s*,\s*(?<domainQuote>'|"")(?<domain>(?:\\.|(?!\k<domainQuote>).)*)\k<domainQuote>"
    foreach ($match in [regex]::Matches($Content, $pattern)) {
        $matchDomain = Convert-PHPStringLiteral $match.Groups['domain'].Value
        if ($matchDomain -ne $Domain) {
            continue
        }

        $msgid = Convert-PHPStringLiteral $match.Groups['msg'].Value
        $key = New-EntryKey -Context '' -Singular $msgid -Plural ''
        if (-not $Entries.ContainsKey($key)) {
            $Entries[$key] = @{
                Context = ''
                Singular = $msgid
                Plural = ''
                References = New-Object System.Collections.Generic.List[string]
            }
        }

        $line = Get-LineNumber -Content $Content -Index $match.Index
        Add-EntryReference -Entry $Entries[$key] -Reference ('{0}:{1}' -f $RelativePath, $line)
    }
}

function Add-ContextMatches {
    param(
        [Parameter(Mandatory = $true)]
        [hashtable] $Entries,
        [Parameter(Mandatory = $true)]
        [string] $Content,
        [Parameter(Mandatory = $true)]
        [string] $RelativePath,
        [Parameter(Mandatory = $true)]
        [string] $Domain
    )

    $pattern = "(?s)(?<func>_x|_ex|esc_html_x|esc_attr_x)\(\s*(?<msgQuote>'|"")(?<msg>(?:\\.|(?!\k<msgQuote>).)*)\k<msgQuote>\s*,\s*(?<ctxQuote>'|"")(?<ctx>(?:\\.|(?!\k<ctxQuote>).)*)\k<ctxQuote>\s*,\s*(?<domainQuote>'|"")(?<domain>(?:\\.|(?!\k<domainQuote>).)*)\k<domainQuote>"
    foreach ($match in [regex]::Matches($Content, $pattern)) {
        $matchDomain = Convert-PHPStringLiteral $match.Groups['domain'].Value
        if ($matchDomain -ne $Domain) {
            continue
        }

        $context = Convert-PHPStringLiteral $match.Groups['ctx'].Value
        $msgid = Convert-PHPStringLiteral $match.Groups['msg'].Value
        $key = New-EntryKey -Context $context -Singular $msgid -Plural ''
        if (-not $Entries.ContainsKey($key)) {
            $Entries[$key] = @{
                Context = $context
                Singular = $msgid
                Plural = ''
                References = New-Object System.Collections.Generic.List[string]
            }
        }

        $line = Get-LineNumber -Content $Content -Index $match.Index
        Add-EntryReference -Entry $Entries[$key] -Reference ('{0}:{1}' -f $RelativePath, $line)
    }
}

function Add-PluralMatches {
    param(
        [Parameter(Mandatory = $true)]
        [hashtable] $Entries,
        [Parameter(Mandatory = $true)]
        [string] $Content,
        [Parameter(Mandatory = $true)]
        [string] $RelativePath,
        [Parameter(Mandatory = $true)]
        [string] $Domain
    )

    $pattern = "(?s)(?<func>_n)\(\s*(?<singQuote>'|"")(?<sing>(?:\\.|(?!\k<singQuote>).)*)\k<singQuote>\s*,\s*(?<pluralQuote>'|"")(?<plural>(?:\\.|(?!\k<pluralQuote>).)*)\k<pluralQuote>\s*,\s*.+?,\s*(?<domainQuote>'|"")(?<domain>(?:\\.|(?!\k<domainQuote>).)*)\k<domainQuote>"
    foreach ($match in [regex]::Matches($Content, $pattern)) {
        $matchDomain = Convert-PHPStringLiteral $match.Groups['domain'].Value
        if ($matchDomain -ne $Domain) {
            continue
        }

        $singular = Convert-PHPStringLiteral $match.Groups['sing'].Value
        $plural = Convert-PHPStringLiteral $match.Groups['plural'].Value
        $key = New-EntryKey -Context '' -Singular $singular -Plural $plural
        if (-not $Entries.ContainsKey($key)) {
            $Entries[$key] = @{
                Context = ''
                Singular = $singular
                Plural = $plural
                References = New-Object System.Collections.Generic.List[string]
            }
        }

        $line = Get-LineNumber -Content $Content -Index $match.Index
        Add-EntryReference -Entry $Entries[$key] -Reference ('{0}:{1}' -f $RelativePath, $line)
    }

    $patternWithContext = "(?s)(?<func>_nx)\(\s*(?<singQuote>'|"")(?<sing>(?:\\.|(?!\k<singQuote>).)*)\k<singQuote>\s*,\s*(?<pluralQuote>'|"")(?<plural>(?:\\.|(?!\k<pluralQuote>).)*)\k<pluralQuote>\s*,\s*.+?,\s*(?<ctxQuote>'|"")(?<ctx>(?:\\.|(?!\k<ctxQuote>).)*)\k<ctxQuote>\s*,\s*(?<domainQuote>'|"")(?<domain>(?:\\.|(?!\k<domainQuote>).)*)\k<domainQuote>"
    foreach ($match in [regex]::Matches($Content, $patternWithContext)) {
        $matchDomain = Convert-PHPStringLiteral $match.Groups['domain'].Value
        if ($matchDomain -ne $Domain) {
            continue
        }

        $context = Convert-PHPStringLiteral $match.Groups['ctx'].Value
        $singular = Convert-PHPStringLiteral $match.Groups['sing'].Value
        $plural = Convert-PHPStringLiteral $match.Groups['plural'].Value
        $key = New-EntryKey -Context $context -Singular $singular -Plural $plural
        if (-not $Entries.ContainsKey($key)) {
            $Entries[$key] = @{
                Context = $context
                Singular = $singular
                Plural = $plural
                References = New-Object System.Collections.Generic.List[string]
            }
        }

        $line = Get-LineNumber -Content $Content -Index $match.Index
        Add-EntryReference -Entry $Entries[$key] -Reference ('{0}:{1}' -f $RelativePath, $line)
    }
}

function Write-PotFile {
    param(
        [Parameter(Mandatory = $true)]
        [string] $OutputPath,
        [Parameter(Mandatory = $true)]
        [string] $PackageName,
        [Parameter(Mandatory = $true)]
        [string] $Domain,
        [Parameter(Mandatory = $true)]
        [hashtable] $Entries
    )

    $outputDir = Split-Path -Parent $OutputPath
    if (-not (Test-Path -LiteralPath $outputDir)) {
        New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
    }

    $generatedAt = [DateTime]::UtcNow.ToString('yyyy-MM-dd HH:mm+0000')
    $builder = New-Object System.Text.StringBuilder
    [void] $builder.AppendLine('msgid ""')
    [void] $builder.AppendLine('msgstr ""')
    [void] $builder.AppendLine(('\"Project-Id-Version: {0}\n\"' -f (Escape-PoString $PackageName)))
    [void] $builder.AppendLine('\"Report-Msgid-Bugs-To: https://github.com/jo-286/wine-e-label/issues\n\"')
    [void] $builder.AppendLine(('\"POT-Creation-Date: {0}\n\"' -f $generatedAt))
    [void] $builder.AppendLine('\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n\"')
    [void] $builder.AppendLine('\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n\"')
    [void] $builder.AppendLine('\"Language-Team: LANGUAGE <EMAIL@ADDRESS>\n\"')
    [void] $builder.AppendLine('\"MIME-Version: 1.0\n\"')
    [void] $builder.AppendLine('\"Content-Type: text/plain; charset=UTF-8\n\"')
    [void] $builder.AppendLine('\"Content-Transfer-Encoding: 8bit\n\"')
    [void] $builder.AppendLine(('\"X-Domain: {0}\n\"' -f (Escape-PoString $Domain)))
    [void] $builder.AppendLine('')

    $sortedEntries = $Entries.GetEnumerator() | Sort-Object {
        '{0}|{1}|{2}' -f $_.Value.Context, $_.Value.Singular, $_.Value.Plural
    }

    foreach ($entryItem in $sortedEntries) {
        $entry = $entryItem.Value
        foreach ($reference in ($entry.References | Sort-Object)) {
            [void] $builder.AppendLine(('#: {0}' -f $reference))
        }

        if ($entry.Context -ne '') {
            [void] $builder.AppendLine(('msgctxt "{0}"' -f (Escape-PoString $entry.Context)))
        }

        [void] $builder.AppendLine(('msgid "{0}"' -f (Escape-PoString $entry.Singular)))

        if ($entry.Plural -ne '') {
            [void] $builder.AppendLine(('msgid_plural "{0}"' -f (Escape-PoString $entry.Plural)))
            [void] $builder.AppendLine('msgstr[0] ""')
            [void] $builder.AppendLine('msgstr[1] ""')
        } else {
            [void] $builder.AppendLine('msgstr ""')
        }

        [void] $builder.AppendLine('')
    }

    [System.IO.File]::WriteAllText($OutputPath, $builder.ToString(), $utf8NoBom)
}

foreach ($target in $templateTargets) {
    $entries = New-EntryMap
    $rootPath = [System.IO.Path]::GetFullPath($target.Root)

    $phpFiles = Get-ChildItem -LiteralPath $rootPath -Recurse -File -Filter '*.php' |
        Where-Object {
            $_.FullName -notmatch '\\vendor\\' -and
            $_.FullName -notmatch '\\.git\\' -and
            $_.FullName -notmatch '\\dist\\' -and
            $_.FullName -notmatch '\\.build-temp\\'
        }

    foreach ($file in $phpFiles) {
        $content = [System.IO.File]::ReadAllText($file.FullName, $utf8NoBom)
        $relativePath = Get-RelativePath -BasePath $rootPath -TargetPath $file.FullName
        $relativePath = $relativePath -replace '\\', '/'
        Add-SimpleMatches -Entries $entries -Content $content -RelativePath $relativePath -Domain $target.Domain
        Add-ContextMatches -Entries $entries -Content $content -RelativePath $relativePath -Domain $target.Domain
        Add-PluralMatches -Entries $entries -Content $content -RelativePath $relativePath -Domain $target.Domain
    }

    Write-PotFile -OutputPath $target.Output -PackageName $target.Package -Domain $target.Domain -Entries $entries
    Write-Host ('Generated translation template: {0}' -f $target.Output)
}
