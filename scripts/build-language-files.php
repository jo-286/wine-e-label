<?php

declare(strict_types=1);

$repoRoot = dirname(__DIR__);
$mainRoot = $repoRoot . DIRECTORY_SEPARATOR . 'Wine-E-Label-v2.3.1';
$receiverRoot = $repoRoot . DIRECTORY_SEPARATOR . 'Wine-E-Label-Receiver-v2.3.1' . DIRECTORY_SEPARATOR . 'wine-e-label-receiver';

if (!defined('ABSPATH')) {
    define('ABSPATH', $repoRoot . DIRECTORY_SEPARATOR);
}
if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}

function plugin_basename(string $file): string { return basename($file); }
function wp_parse_args($args, $defaults = array()) { return array_merge($defaults, is_array($args) ? $args : array()); }
function add_filter(...$args): void {}
function add_action(...$args): void {}
function admin_url(string $path = ''): string { return 'https://example.test/wp-admin/' . ltrim($path, '/'); }
function is_admin(): bool { return true; }
function wp_doing_ajax(): bool { return false; }
function determine_locale(): string { return 'de_DE'; }
function get_user_locale(): string { return 'de_DE'; }
function get_locale(): string { return 'de_DE'; }
function get_option(string $name, $default = false) { return $default; }
function delete_site_transient(string $key): void {}
function get_site_transient(string $key) { return false; }
function set_site_transient(string $key, $value, int $expiration): void {}
function wp_safe_remote_get(string $url, array $args = array()) { return array('response' => array('code' => 200), 'body' => '{}'); }
function is_wp_error($thing): bool { return false; }
function wp_remote_retrieve_response_code($response): int { return (int) ($response['response']['code'] ?? 200); }
function wp_remote_retrieve_body($response): string { return (string) ($response['body'] ?? '{}'); }

require_once $mainRoot . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wine-e-label-admin-i18n.php';
/**
 * @return array<string,array<string,string>>
 */
function getMainTranslations(): array
{
    $reflection = new ReflectionClass('Wine_E_Label_Admin_I18n');
    $method = $reflection->getMethod('translations');
    $method->setAccessible(true);
    /** @var array<string,array<string,string>> $translations */
    $translations = $method->invoke(null);
    return $translations;
}

/**
 * @return array<string,array<string,string>>
 */
function getReceiverTranslations(): array
{
    global $receiverRoot;
    $source = file_get_contents($receiverRoot . DIRECTORY_SEPARATOR . 'wine-e-label-receiver.php');
    if ($source === false) {
        throw new RuntimeException('Could not read receiver plugin file.');
    }

    $anchor = 'private static array $translations = array(';
    $start = strpos($source, $anchor);
    if ($start === false) {
        throw new RuntimeException('Could not locate receiver translation map.');
    }

    $arrayStart = $start + strlen('private static array $translations = ');
    $arrayCode = extractPhpArrayLiteral($source, $arrayStart);
    /** @var array<string,array<string,string>> $translationsByLanguage */
    $translationsByLanguage = eval('return ' . $arrayCode . ';');
    if (!is_array($translationsByLanguage)) {
        throw new RuntimeException('Receiver translation map is invalid.');
    }

    $entries = array();
    foreach ($translationsByLanguage as $language => $values) {
        foreach ($values as $key => $value) {
            if (!isset($entries[$key])) {
                $entries[$key] = array();
            }
            $entries[$key][$language] = (string) $value;
        }
    }

    return $entries;
}

function extractPhpArrayLiteral(string $source, int $offset): string
{
    $length = strlen($source);
    $depth = 0;
    $inSingle = false;
    $inDouble = false;
    $escaped = false;
    $buffer = '';

    for ($i = $offset; $i < $length; $i++) {
        $char = $source[$i];
        $buffer .= $char;

        if ($escaped) {
            $escaped = false;
            continue;
        }

        if ($char === '\\') {
            $escaped = true;
            continue;
        }

        if ($inSingle) {
            if ($char === "'") {
                $inSingle = false;
            }
            continue;
        }

        if ($inDouble) {
            if ($char === '"') {
                $inDouble = false;
            }
            continue;
        }

        if ($char === "'") {
            $inSingle = true;
            continue;
        }

        if ($char === '"') {
            $inDouble = true;
            continue;
        }

        if ($char === '(' || $char === '[') {
            $depth++;
            continue;
        }

        if ($char === ')' || $char === ']') {
            $depth--;
            if ($depth === 0) {
                return rtrim($buffer);
            }
        }
    }

    throw new RuntimeException('Failed to extract PHP array literal.');
}

function getLocaleMap(): array
{
    return array(
        'de' => array('de_DE', 'de_DE_formal'),
        'en' => array('en_US'),
        'fr' => array('fr_FR'),
        'it' => array('it_IT'),
    );
}

function getPluralForms(string $locale): string
{
    if (str_starts_with($locale, 'fr')) {
        return 'nplurals=2; plural=(n > 1);';
    }
    return 'nplurals=2; plural=(n != 1);';
}

function escapePo(string $value): string
{
    $value = str_replace('\\', '\\\\', $value);
    $value = str_replace('"', '\\"', $value);
    $value = str_replace("\r", '\r', $value);
    $value = str_replace("\n", '\n', $value);
    return $value;
}

/**
 * @param array<string,string> $translations
 */
function buildPoContent(string $domain, string $locale, array $translations): string
{
    ksort($translations, SORT_NATURAL | SORT_FLAG_CASE);
    $lines = array(
        'msgid ""',
        'msgstr ""',
        '"Project-Id-Version: ' . escapePo($domain) . '\n"',
        '"POT-Creation-Date: ' . gmdate('Y-m-d H:i+0000') . '\n"',
        '"PO-Revision-Date: ' . gmdate('Y-m-d H:i+0000') . '\n"',
        '"Last-Translator: Wine E-Label\n"',
        '"Language-Team: Wine E-Label\n"',
        '"Language: ' . escapePo($locale) . '\n"',
        '"MIME-Version: 1.0\n"',
        '"Content-Type: text/plain; charset=UTF-8\n"',
        '"Content-Transfer-Encoding: 8bit\n"',
        '"Plural-Forms: ' . getPluralForms($locale) . '\n"',
        '',
    );

    foreach ($translations as $source => $target) {
        $lines[] = 'msgid "' . escapePo($source) . '"';
        $lines[] = 'msgstr "' . escapePo($target) . '"';
        $lines[] = '';
    }

    return implode("\n", $lines) . "\n";
}

/**
 * @param array<string,string> $translations
 */
function buildMoBinary(array $translations): string
{
    ksort($translations, SORT_STRING);
    $keys = array_keys($translations);
    $values = array_values($translations);
    $count = count($translations);

    $ids = '';
    $strings = '';
    $offsets = array();
    $tableOffsetOriginal = 28;
    $tableOffsetTranslated = $tableOffsetOriginal + ($count * 8);
    $currentOriginalOffset = $tableOffsetTranslated + ($count * 8);
    $currentTranslatedOffset = $currentOriginalOffset;

    foreach ($keys as $key) {
        $ids .= $key . "\0";
    }
    $currentTranslatedOffset += strlen($ids);

    foreach ($values as $value) {
        $strings .= $value . "\0";
    }

    $originalEntries = '';
    $translatedEntries = '';
    $runningOriginalOffset = $tableOffsetTranslated + ($count * 8);
    foreach ($keys as $index => $key) {
        $length = strlen($key);
        $originalEntries .= pack('V2', $length, $runningOriginalOffset);
        $runningOriginalOffset += $length + 1;
    }

    $runningTranslatedOffset = $currentTranslatedOffset;
    foreach ($values as $value) {
        $length = strlen($value);
        $translatedEntries .= pack('V2', $length, $runningTranslatedOffset);
        $runningTranslatedOffset += $length + 1;
    }

    return pack(
        'V7',
        0x950412de,
        0,
        $count,
        $tableOffsetOriginal,
        $tableOffsetTranslated,
        0,
        0
    ) . $originalEntries . $translatedEntries . $ids . $strings;
}

/**
 * @param array<string,array<string,string>> $entries
 */
function writeLocaleFiles(string $outputDir, string $domain, array $entries): void
{
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    foreach (getLocaleMap() as $language => $locales) {
        $translations = array();
        foreach ($entries as $source => $entry) {
            $target = $entry[$language] ?? $entry['en'] ?? $entry['de'] ?? $source;
            $translations[$source] = $target === '' ? $source : $target;
        }

        foreach ($locales as $locale) {
            $poPath = $outputDir . DIRECTORY_SEPARATOR . $domain . '-' . $locale . '.po';
            $moPath = $outputDir . DIRECTORY_SEPARATOR . $domain . '-' . $locale . '.mo';
            file_put_contents($poPath, buildPoContent($domain, $locale, $translations));
            file_put_contents($moPath, buildMoBinary($translations));
            echo 'Built language files: ' . $poPath . PHP_EOL;
            echo 'Built language files: ' . $moPath . PHP_EOL;
        }
    }
}

writeLocaleFiles($mainRoot . DIRECTORY_SEPARATOR . 'languages', 'wine-e-label', getMainTranslations());
writeLocaleFiles($receiverRoot . DIRECTORY_SEPARATOR . 'languages', 'wine-e-label-receiver', getReceiverTranslations());
