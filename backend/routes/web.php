<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return response()->json(['message' => 'Psiké Deloun Arts API']);
});

// Rota para servir imagens do catálogo (assets/images)
Route::get('/assets/images/{path}', function ($path) {
    $fullPath = base_path('assets/images/' . $path);
    
    if (!file_exists($fullPath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($fullPath);
    
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');
