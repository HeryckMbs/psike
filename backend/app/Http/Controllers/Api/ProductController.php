<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'mainImage', 'images'])->where('active', true);

        // Filtro por categoria
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por tipo
        if ($request->has('type')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->type);
            });
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        return ProductResource::collection($products);
    }

    public function adminIndex(Request $request)
    {
        $query = Product::with(['category', 'mainImage', 'images']);

        // Filtro por categoria
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por tipo
        if ($request->has('type')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->type);
            });
        }

        // Admin pode ver produtos inativos também
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        return ProductResource::collection($products);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'options', 'prices'])
            ->where('active', true)
            ->findOrFail($id);

        return new ProductResource($product);
    }

    public function related($id)
    {
        $product = Product::findOrFail($id);
        
        // Buscar produtos da mesma categoria, excluindo o produto atual
        $relatedProducts = Product::with(['category', 'mainImage', 'images'])
            ->where('active', true)
            ->where('id', '!=', $id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Se não houver produtos suficientes na mesma categoria, buscar de outras categorias
        if ($relatedProducts->count() < 4) {
            $additionalProducts = Product::with(['category', 'mainImage', 'images'])
                ->where('active', true)
                ->where('id', '!=', $id)
                ->where('category_id', '!=', $product->category_id)
                ->inRandomOrder()
                ->limit(4 - $relatedProducts->count())
                ->get();
            
            $relatedProducts = $relatedProducts->merge($additionalProducts);
        }

        return ProductResource::collection($relatedProducts);
    }

    public function adminShow($id)
    {
        $product = Product::with(['category', 'box', 'images', 'options', 'prices'])
            ->findOrFail($id);

        return new ProductResource($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'base_id' => 'nullable|string|max:255|unique:products,base_id',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'tenda_code' => 'nullable|string',
            'can_calculate_price' => 'boolean',
            'price_per_square_meter' => 'nullable|numeric',
            'fixed_price' => 'nullable|numeric',
            'box_id' => 'nullable|exists:boxes,id',
        ]);

        // Se slug não foi fornecido, será gerado automaticamente pelo modelo
        // Remover slug do validated se estiver vazio para permitir geração automática
        if (isset($validated['slug']) && empty($validated['slug'])) {
            unset($validated['slug']);
        }

        // Se base_id não foi fornecido, será gerado automaticamente pelo modelo
        // Remover base_id do validated se estiver vazio para permitir geração automática
        if (isset($validated['base_id']) && empty($validated['base_id'])) {
            unset($validated['base_id']);
        }

        $product = Product::create($validated);

        return new ProductResource($product->load(['category', 'images']));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
            'base_id' => 'nullable|string|max:255|unique:products,base_id,' . $id,
            'width' => 'sometimes|numeric|min:0',
            'height' => 'sometimes|numeric|min:0',
            'tenda_code' => 'nullable|string',
            'can_calculate_price' => 'sometimes|boolean',
            'price_per_square_meter' => 'nullable|numeric',
            'fixed_price' => 'nullable|numeric',
            'box_id' => 'nullable|exists:boxes,id',
        ]);

        // Se slug foi fornecido, usar; caso contrário, será gerado automaticamente pelo modelo
        // Remover slug do validated se estiver vazio para permitir geração automática
        if (isset($validated['slug']) && empty($validated['slug'])) {
            unset($validated['slug']);
        }

        // Se base_id foi fornecido, usar; caso contrário, será gerado automaticamente pelo modelo
        // Remover base_id do validated se estiver vazio para permitir geração automática
        if (isset($validated['base_id']) && empty($validated['base_id'])) {
            unset($validated['base_id']);
        }

        $product->update($validated);

        return new ProductResource($product->load(['category', 'images']));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function updateOptions(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        // Implementar lógica de atualização de opções
        return response()->json(['message' => 'Options updated']);
    }

    public function updatePrices(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        // Implementar lógica de atualização de preços
        return response()->json(['message' => 'Prices updated']);
    }

    public function uploadImages(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        // Verificar limite de 7 imagens
        $currentImageCount = $product->images()->count();
        $maxNewImages = max(0, 7 - $currentImageCount);
        
        if ($maxNewImages === 0) {
            return response()->json(['message' => 'Limite de 7 imagens atingido'], 422);
        }
        
        // Aceitar tanto 'images' quanto 'images[]' ou 'images[0]', 'images[1]', etc.
        $files = $request->file('images');
        
        // Se não encontrou em 'images', tentar 'images[]'
        if (!$files) {
            $files = $request->file('images[]');
        }
        
        // Se ainda não encontrou, verificar se há arquivos com índices numéricos
        if (!$files || (is_array($files) && empty($files))) {
            $allFiles = $request->allFiles();
            $files = [];
            foreach ($allFiles as $key => $file) {
                if (str_starts_with($key, 'images')) {
                    if (is_array($file)) {
                        $files = array_merge($files, $file);
                    } else {
                        $files[] = $file;
                    }
                }
            }
        }
        
        if (empty($files)) {
            return response()->json(['message' => 'Nenhuma imagem foi enviada. Por favor, selecione pelo menos uma imagem.'], 422);
        }
        
        // Converter para array se necessário
        if (!is_array($files)) {
            $files = [$files];
        }
        
        // Filtrar valores nulos
        $files = array_filter($files, function($file) {
            return $file !== null;
        });
        
        if (empty($files)) {
            return response()->json(['message' => 'Nenhuma imagem válida foi enviada.'], 422);
        }
        
        // Validar quantidade
        if (count($files) > $maxNewImages) {
            return response()->json([
                'message' => "Você pode adicionar apenas {$maxNewImages} imagem(ns)"
            ], 422);
        }
        
        // Validar cada arquivo
        foreach ($files as $index => $file) {
            if (!$file->isValid()) {
                return response()->json(['message' => 'Arquivo inválido: ' . $file->getClientOriginalName()], 422);
            }
            
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json(['message' => 'Tipo de arquivo não permitido: ' . $file->getClientOriginalName()], 422);
            }
            
            if ($file->getSize() > 5120 * 1024) { // 5MB
                return response()->json(['message' => 'Arquivo muito grande (máx 5MB): ' . $file->getClientOriginalName()], 422);
            }
        }

        $uploadedImages = [];
        
        try {
            foreach ($files as $index => $file) {
                // Sanitizar nome do arquivo
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                
                // Criar diretório se não existir
                $directory = 'products/' . $product->id;
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                
                // Fazer upload do arquivo
                $path = $file->storeAs($directory, $filename, 'public');
                
                if (!$path) {
                    Log::error("Failed to store image: {$filename}");
                    continue;
                }
                
                // Criar registro no banco
                $image = ProductImage::create([
                    'product_id' => $product->id,
                    'path' => Storage::url($path),
                    'filename' => $filename,
                    'is_main' => $index === 0 && $product->images()->where('is_main', true)->count() === 0,
                    'order' => ($product->images()->max('order') ?? 0) + 1 + $index,
                ]);
                
                $uploadedImages[] = $image;
            }
        } catch (\Exception $e) {
            Log::error("Error uploading images: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json([
                'message' => 'Erro ao fazer upload das imagens: ' . $e->getMessage()
            ], 500);
        }

        if (empty($uploadedImages)) {
            return response()->json([
                'message' => 'Nenhuma imagem foi processada com sucesso'
            ], 422);
        }

        return response()->json([
            'message' => 'Imagens enviadas com sucesso',
            'images' => $uploadedImages
        ]);
    }

    public function deleteImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        $image = ProductImage::where('product_id', $product->id)->findOrFail($imageId);
        
        // Deletar arquivo físico
        $filePath = str_replace('/storage/', '', $image->path);
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        
        $image->delete();
        
        return response()->json(['message' => 'Imagem deletada com sucesso']);
    }

    public function setMainImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        $image = ProductImage::where('product_id', $product->id)->findOrFail($imageId);
        
        // Remover is_main de todas as imagens do produto
        ProductImage::where('product_id', $product->id)->update(['is_main' => false]);
        
        // Definir esta como principal
        $image->update(['is_main' => true]);
        
        return response()->json(['message' => 'Imagem principal definida com sucesso']);
    }

    public function reorderImages(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:product_images,id',
            'images.*.order' => 'required|integer',
        ]);

        foreach ($request->images as $imageData) {
            ProductImage::where('id', $imageData['id'])
                ->where('product_id', $product->id)
                ->update(['order' => $imageData['order']]);
        }

        return response()->json(['message' => 'Ordem das imagens atualizada']);
    }
}
