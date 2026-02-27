<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Login endpoint - cria personal access token
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::guard('web')->attempt($credentials)) {
        $user = Auth::guard('web')->user();
        
        // Criar personal access token usando Passport
        // Personal access tokens não requerem cliente OAuth
        $token = $user->createToken('Admin Token', ['*'])->accessToken;
        
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    return response()->json(['message' => 'Credenciais inválidas'], 401);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::prefix('products')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::get('/{id}/related', [App\Http\Controllers\Api\ProductController::class, 'related']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\CategoryController::class, 'index']);
});

Route::prefix('boxes')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\BoxController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\BoxController::class, 'show']);
});

Route::prefix('posts')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\BlogController::class, 'index']);
    Route::get('/{slug}', [App\Http\Controllers\Api\BlogController::class, 'show']);
});

Route::prefix('orders')->group(function () {
    Route::post('/', [App\Http\Controllers\Api\OrderController::class, 'store']);
});

Route::prefix('shipping')->group(function () {
    Route::post('/calculate', [App\Http\Controllers\Api\ShippingController::class, 'calculate']);
    Route::post('/{id}/generate-label', [App\Http\Controllers\Api\ShippingController::class, 'generateLabel'])->middleware('auth:api');
    Route::get('/{id}/track', [App\Http\Controllers\Api\ShippingController::class, 'track'])->middleware('auth:api');
});


// Melhor Envio OAuth
Route::prefix('melhor-envio')->group(function () {
    Route::get('/authorize', [App\Http\Controllers\Api\MelhorEnvioAuthController::class, 'authorize']);
    Route::get('/callback', [App\Http\Controllers\Api\MelhorEnvioAuthController::class, 'callback']);
    Route::get('/status', [App\Http\Controllers\Api\MelhorEnvioAuthController::class, 'status']);
    Route::post('/revoke', [App\Http\Controllers\Api\MelhorEnvioAuthController::class, 'revoke']);
});

// Protected routes (admin)
Route::middleware('auth:api')->prefix('admin')->group(function () {
    // Products
    Route::get('products', [App\Http\Controllers\Api\ProductController::class, 'adminIndex']);
    Route::get('products/{id}', [App\Http\Controllers\Api\ProductController::class, 'adminShow']);
    Route::post('products', [App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::put('products/{id}', [App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::delete('products/{id}', [App\Http\Controllers\Api\ProductController::class, 'destroy']);
    Route::prefix('products/{id}')->group(function () {
        Route::put('/options', [App\Http\Controllers\Api\ProductController::class, 'updateOptions']);
        Route::put('/prices', [App\Http\Controllers\Api\ProductController::class, 'updatePrices']);
        Route::post('/images', [App\Http\Controllers\Api\ProductController::class, 'uploadImages']);
        Route::delete('/images/{imageId}', [App\Http\Controllers\Api\ProductController::class, 'deleteImage']);
        Route::put('/images/{imageId}/main', [App\Http\Controllers\Api\ProductController::class, 'setMainImage']);
        Route::put('/images/reorder', [App\Http\Controllers\Api\ProductController::class, 'reorderImages']);
    });

    // Categories
    Route::get('categories', [App\Http\Controllers\Api\CategoryController::class, 'adminIndex']);
    Route::get('categories/{id}', [App\Http\Controllers\Api\CategoryController::class, 'show']);
    Route::post('categories', [App\Http\Controllers\Api\CategoryController::class, 'store']);
    Route::put('categories/{id}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
    Route::delete('categories/{id}', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);

    // Boxes
    Route::get('boxes', [App\Http\Controllers\Api\BoxController::class, 'adminIndex']);
    Route::get('boxes/{id}', [App\Http\Controllers\Api\BoxController::class, 'show']);
    Route::post('boxes', [App\Http\Controllers\Api\BoxController::class, 'store']);
    Route::put('boxes/{id}', [App\Http\Controllers\Api\BoxController::class, 'update']);
    Route::delete('boxes/{id}', [App\Http\Controllers\Api\BoxController::class, 'destroy']);

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\OrderController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\OrderController::class, 'show']);
        Route::put('/{id}/status', [App\Http\Controllers\Api\OrderController::class, 'updateStatus']);
        Route::put('/{id}/dimensions', [App\Http\Controllers\Api\OrderController::class, 'updateDimensions']);
        Route::put('/{id}/custom-price', [App\Http\Controllers\Api\OrderController::class, 'updateCustomPrice']);
        Route::get('/{id}/pdf', [App\Http\Controllers\Api\OrderController::class, 'generatePdf']);
        Route::post('/{orderId}/shipping', [App\Http\Controllers\Api\ShippingController::class, 'saveShippingForOrder']);
        
        // Order Costs
        Route::prefix('{orderId}/costs')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\OrderCostController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\OrderCostController::class, 'store']);
            Route::put('/{costId}', [App\Http\Controllers\Api\OrderCostController::class, 'update']);
            Route::delete('/{costId}', [App\Http\Controllers\Api\OrderCostController::class, 'destroy']);
        });
    });

    // Shipping (Admin)
    Route::prefix('shipping')->group(function () {
        Route::post('/{id}/generate-label', [App\Http\Controllers\Api\ShippingController::class, 'generateLabel']);
        Route::get('/{id}/track', [App\Http\Controllers\Api\ShippingController::class, 'track']);
    });

    // Cost Types
    Route::apiResource('cost-types', App\Http\Controllers\Api\CostTypeController::class);

    // Kanban
    Route::prefix('kanban')->group(function () {
        Route::get('/orders', [App\Http\Controllers\Api\KanbanController::class, 'getOrders']);
        Route::put('/orders/{id}/status', [App\Http\Controllers\Api\KanbanController::class, 'updateOrderStatus']);
        Route::get('/statuses', [App\Http\Controllers\Api\KanbanController::class, 'getStatuses']);
        Route::apiResource('statuses', App\Http\Controllers\Api\KanbanController::class);
    });

    // Blog
    Route::apiResource('posts', App\Http\Controllers\Api\BlogController::class)->except(['index', 'show']);
});

// Rota temporária para redimensionar imagem de fundo para A4
Route::get('/admin/resize-background-image', function () {
    $sourceImage = public_path('NOVO PAPEL TIMBRADO PSIKE DELOUN.jpg');
    $targetWidth = 794;  // A4 width at 96 DPI
    $targetHeight = 1123; // A4 height at 96 DPI
    
    if (!file_exists($sourceImage)) {
        return response()->json(['error' => 'Imagem não encontrada'], 404);
    }
    
    // Carregar imagem
    $image = @imagecreatefromjpeg($sourceImage);
    if (!$image) {
        return response()->json(['error' => 'Não foi possível carregar a imagem'], 500);
    }
    
    $originalWidth = imagesx($image);
    $originalHeight = imagesy($image);
    
    // Criar nova imagem
    $newImage = imagecreatetruecolor($targetWidth, $targetHeight);
    
    // Redimensionar com alta qualidade
    imagecopyresampled(
        $newImage, $image,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $originalWidth, $originalHeight
    );
    
    // Salvar
    $success = imagejpeg($newImage, $sourceImage, 85);
    imagedestroy($image);
    imagedestroy($newImage);
    
    if ($success) {
        $fileSize = filesize($sourceImage);
        return response()->json([
            'success' => true,
            'message' => 'Imagem redimensionada com sucesso para A4 (794x1123px)',
            'original_size' => "{$originalWidth}x{$originalHeight}",
            'new_size' => "{$targetWidth}x{$targetHeight}",
            'file_size_kb' => round($fileSize / 1024, 2)
        ]);
    }
    
    return response()->json(['error' => 'Erro ao salvar imagem'], 500);
})->middleware('auth:sanctum');
