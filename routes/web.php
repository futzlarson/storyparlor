<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Mail\Markdown;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\ImportController;

Route::get('readme', function () {
    $content = File::get(base_path('README.md'));
    $html = Markdown::parse($content);

    return response($html, 200)
        ->header('Content-Type', 'text/html');
});

Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Database connection successful',
            'database' => $dbName,
            'driver' => config('database.default')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/', [CheckinController::class, 'show']);
Route::post('/', [CheckinController::class, 'process']);

Route::get('import', [ImportController::class, 'show']);
Route::post('import', [ImportController::class, 'process']);