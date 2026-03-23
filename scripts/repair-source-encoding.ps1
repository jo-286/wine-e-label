param(
    [string[]] $Roots = @(
        'C:\Users\Hase\OneDrive - Weingut Reith\Desktop\Wine E-Label\Wine-E-Label-v2.3.1',
        'C:\Users\Hase\OneDrive - Weingut Reith\Desktop\Wine E-Label\Wine-E-Label-Receiver-v2.3.1\wine-e-label-receiver'
    )
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$utf8 = [System.Text.UTF8Encoding]::new($false)
$cp1252 = [System.Text.Encoding]::GetEncoding(1252)
$extensions = @('.php', '.js', '.css', '.md', '.txt')
$suspiciousPatterns = @(
    ([char]0x00C3).ToString(),
    ([char]0x00C2).ToString(),
    ([char]0x00E2).ToString()
)

function Get-SuspiciousScore {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Text
    )

    $score = 0
    foreach ($pattern in $suspiciousPatterns) {
        $score += ([regex]::Matches($Text, [regex]::Escape($pattern))).Count
    }
    return $score
}

function Get-RepairedCandidate {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Text
    )

    $current = $Text
    for ($i = 0; $i -lt 3; $i++) {
        $candidate = $utf8.GetString($cp1252.GetBytes($current))
        if ((Get-SuspiciousScore -Text $candidate) -ge (Get-SuspiciousScore -Text $current)) {
            break
        }
        $current = $candidate
    }
    return $current
}

$changed = [System.Collections.Generic.List[string]]::new()

foreach ($root in $Roots) {
    if (-not (Test-Path -LiteralPath $root)) {
        continue
    }

    $files = Get-ChildItem -LiteralPath $root -Recurse -File | Where-Object {
        $extensions -contains $_.Extension.ToLowerInvariant()
    }

    foreach ($file in $files) {
        $content = [System.IO.File]::ReadAllText($file.FullName, $utf8)
        $originalScore = Get-SuspiciousScore -Text $content
        if ($originalScore -eq 0) {
            continue
        }

        $candidate = Get-RepairedCandidate -Text $content
        $candidateScore = Get-SuspiciousScore -Text $candidate

        if ($candidateScore -lt $originalScore) {
            [System.IO.File]::WriteAllText($file.FullName, $candidate, $utf8)
            $changed.Add($file.FullName) | Out-Null
        }
    }
}

if ($changed.Count -eq 0) {
    Write-Host 'No encoding repairs were necessary.'
} else {
    Write-Host ('Repaired {0} file(s):' -f $changed.Count)
    foreach ($path in $changed) {
        Write-Host (' - {0}' -f $path)
    }
}
