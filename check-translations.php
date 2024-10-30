<?php

// Adapted from: https://github.com/barryvdh/laravel-translation-manager/blob/master/src/Manager.php
$translationKeys = [];
$functions = ['@lang', '__'];
$stringPattern =
    "[^\w]" .                                       // Must not have an alphanum before real method
    '(' . implode('|', $functions) . ')' .              // Must start with one of the functions
    "\(\s*" .                                       // Match opening parenthesis
    "(?P<quote>['\"])" .                            // Match " or ' and store in {quote}
    "(?P<string>(?:\\\k{quote}|(?!\k{quote}).)*)" . // Match any string that can be {quote} escaped
    "\k{quote}" .                                   // Match " or ' previously matched
    "\s*[\),]";                                    // Close parentheses or new parameter

$labelPattern =
    'label=' .                                     // Match `label=`
    "(?P<quote>['\"])" .                            // Match " or ' and store in {quote}
    "(?P<string>(?:\\\k{quote}|(?!\k{quote}).)*)" . // Match any string that can be {quote} escaped
    "\k{quote}";                                   // Match " or ' previously matched

$files = [];
$iterator = new RecursiveDirectoryIterator('resources');
foreach (new RecursiveIteratorIterator($iterator) as $file) {
    if (strpos($file, '.blade.php') !== false) {
        $files[] = $file->getRealPath();
    }
}

foreach ($files as $file) {
    $contents = file_get_contents($file);
    if (preg_match_all("/{$stringPattern}/siU", $contents, $matches)) {
        foreach ($matches['string'] as $key) {
            $translationKeys[] = $key;
        }
    }
    if (preg_match_all("/{$labelPattern}/siU", $contents, $matches)) {
        foreach ($matches['string'] as $key) {
            if (str_starts_with($key, '@lang(') || str_starts_with($key, '__(') || $key == 'false' || ! $key) {
                continue;
            }
            $translationKeys[] = $key;
        }
    }
}

$translationKeys = array_unique($translationKeys);
$translations = (array) json_decode(file_get_contents('lang/nl.json'));

$missing = 0;
foreach ($translationKeys as $key) {
    if (! array_key_exists($key, $translations)) {
        $missing++;
        error_log("missing translation: \"{$key}\"");
    }
}

if ($missing > 0) {
    if ($missing == 1) {
        throw new Exception('1 translation is missing.');
    } else {
        throw new Exception($missing . ' translations are missing.');
    }
}
