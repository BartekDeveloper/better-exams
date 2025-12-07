<?php
declare(strict_types=1);

if (!defined('RELATIVE_PATH_APP')) {
    define('RELATIVE_PATH_APP', '/better-exams');
}

require_once __DIR__ . '/../view/module/helpers.php';

final class TestFailure extends Exception {}

function assertSameValue($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        $exportExpected = var_export($expected, true);
        $exportActual = var_export($actual, true);
        throw new TestFailure($message . "\nExpected: {$exportExpected}\nActual:   {$exportActual}\n");
    }
}

$tests = [
    'transliterates_accented_characters' => function (): void {
        assertSameValue('zazolc-gesla-jazn', CleanLink('Zażółć Gęślą Jaźń'), 'CleanLink should transliterate accented characters');
    },
    'normalises_spacing_and_case' => function (): void {
        assertSameValue('inf-03', CleanLink(' Inf 03 '), 'CleanLink should normalise casing and spaces to hyphen');
    },
    'strips_non_alphanumeric' => function (): void {
        assertSameValue('sliwka', CleanLink("Śliwka!@#"), 'CleanLink should remove non-alphanumeric characters');
    },
    'collapses_multiple_hyphens' => function (): void {
        assertSameValue('ala-ma-kota', CleanLink('Ala--- ma___kota??'), 'CleanLink should collapse duplicate separators');
    },
    'app_home_path_uses_relative_app' => function (): void {
        assertSameValue('/better-exams/view/index.php', AppHomePath(), 'AppHomePath should target the application home');
    },
];

$failures = [];

foreach ($tests as $name => $test) {
    try {
        $test();
    } catch (TestFailure $failure) {
        $failures[$name] = $failure->getMessage();
    } catch (Throwable $throwable) {
        $failures[$name] = 'Unexpected error: ' . $throwable->getMessage();
    }
}

if (!empty($failures)) {
    fwrite(STDERR, "Test run failed:\n");
    foreach ($failures as $name => $message) {
        fwrite(STDERR, " - {$name}: {$message}\n");
    }
    exit(1);
}

echo "All helper tests passed (" . count($tests) . ")\n";
