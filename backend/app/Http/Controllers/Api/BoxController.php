<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoxController extends Controller
{
    /**
     * Lista todas as caixas (público - apenas ativas)
     */
    public function index(Request $request)
    {
        $query = Box::query();

        // Se não for admin, mostrar apenas ativas
        if (!$request->user() || !$request->user()->tokenCan('admin')) {
            $query->where('active', true);
        }

        $boxes = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $boxes
        ]);
    }

    /**
     * Lista todas as caixas (admin - todas)
     */
    public function adminIndex(Request $request)
    {
        $boxes = Box::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $boxes
        ]);
    }

    /**
     * Mostra uma caixa específica
     */
    public function show($id)
    {
        $box = Box::find($id);

        if (!$box) {
            return response()->json([
                'success' => false,
                'message' => 'Caixa não encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $box
        ]);
    }

    /**
     * Cria uma nova caixa
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'width' => 'required|numeric|min:11', // Mínimo 11cm (requisito Melhor Envio)
            'height' => 'required|numeric|min:11',
            'length' => 'required|numeric|min:11',
            'weight' => 'required|numeric|min:0.1', // Mínimo 0.1kg
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $box = Box::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Caixa criada com sucesso',
            'data' => $box
        ], 201);
    }

    /**
     * Atualiza uma caixa
     */
    public function update(Request $request, $id)
    {
        $box = Box::find($id);

        if (!$box) {
            return response()->json([
                'success' => false,
                'message' => 'Caixa não encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'width' => 'sometimes|required|numeric|min:11',
            'height' => 'sometimes|required|numeric|min:11',
            'length' => 'sometimes|required|numeric|min:11',
            'weight' => 'sometimes|required|numeric|min:0.1',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $box->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Caixa atualizada com sucesso',
            'data' => $box
        ]);
    }

    /**
     * Deleta uma caixa
     */
    public function destroy($id)
    {
        $box = Box::find($id);

        if (!$box) {
            return response()->json([
                'success' => false,
                'message' => 'Caixa não encontrada'
            ], 404);
        }

        // Verificar se há produtos usando esta caixa
        $productsCount = $box->products()->count();
        if ($productsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Não é possível deletar a caixa. Ela está sendo usada por {$productsCount} produto(s)."
            ], 422);
        }

        $box->delete();

        return response()->json([
            'success' => true,
            'message' => 'Caixa deletada com sucesso'
        ]);
    }
}
