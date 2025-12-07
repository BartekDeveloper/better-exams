<?php
if (!function_exists('CleanLink')) {
    function CleanLink(string $value): string {
        $value = strtolower($value);
        $charMap = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
            'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z'
        ];
        $value = strtr($value, $charMap);
        $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
        $value = preg_replace('/[\s-]+/', '-', $value);
        return trim($value, '-');
    }
}

if (!function_exists('AppHomePath')) {
    function AppHomePath(): string {
        $normalized = trim(RELATIVE_PATH_APP, '/\\');
        if ($normalized === '') {
            return '/view/index.php';
        }
        return '/' . $normalized . '/view/index.php';
    }
}

if (!function_exists('RedirectToAppHome')) {
    function RedirectToAppHome(): void {
        header('Location: ' . AppHomePath());
        exit();
    }
}
