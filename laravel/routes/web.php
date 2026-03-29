<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// DEBUG: route for debug loading translations from containers
Route::get('/debug-translations', function () {
    if (!app()->environment('local')) {
        abort(404);
    }

    $debug = [];

    $debug['namespaces'] = Lang::getLoader()->namespaces();

    $locales = ['en', 'ru', 'uk'];

    foreach ($locales as $locale) {
        app()->setLocale($locale);
        $debug[$locale] = [];

        foreach (array_keys($debug['namespaces']) as $namespace) {
            $testKey = "{$namespace}::events";
            $debug[$locale][$namespace] = [
                'translation' => trans($testKey),
                'exists' => trans($testKey) !== $testKey
            ];
        }
    }

    $debug['structure'] = [];
    $shipLanguages = base_path('app/Ship/Languages');
    if (is_dir($shipLanguages)) {
        $debug['structure']['ship'] = scandir($shipLanguages);
    }

    return response()->json($debug);
});
