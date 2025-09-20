<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Mail\Markdown;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\ImportController;

Route::get('readme', function () {
    $content = File::get(base_path('README.md'));
    $html = Markdown::parse($content);

    return response($html, 200)
        ->header('Content-Type', 'text/html');
});

Route::get('/', [CheckinController::class, 'show']);
Route::post('/', [CheckinController::class, 'process']);

Route::get('import', [ImportController::class, 'show']);
Route::post('import', [ImportController::class, 'process']);