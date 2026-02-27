<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CostType;
use Illuminate\Http\Request;

class CostTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = CostType::query();

        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        if ($request->has('active') && $request->active !== '') {
            $query->where('active', $request->boolean('active'));
        } elseif (!$request->has('active') || $request->active === '') {
            // Se não foi passado ou foi passado vazio, mostrar todos
            // Não aplicar filtro de active
        }

        $costTypes = $query->orderBy('type')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return response()->json($costTypes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cost_types,name',
            'type' => 'required|in:receita,despesa',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'order' => 'integer',
        ]);

        $costType = CostType::create($validated);

        return response()->json($costType, 201);
    }

    public function show($id)
    {
        $costType = CostType::findOrFail($id);

        return response()->json($costType);
    }

    public function update(Request $request, $id)
    {
        $costType = CostType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cost_types,name,' . $id,
            'type' => 'required|in:receita,despesa',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'order' => 'integer',
        ]);

        $costType->update($validated);

        return response()->json($costType);
    }

    public function destroy($id)
    {
        $costType = CostType::findOrFail($id);

        // Verificar se há custos usando este tipo
        if ($costType->costs()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir este tipo de custo pois existem custos associados a ele.'
            ], 422);
        }

        $costType->delete();

        return response()->json(['message' => 'Tipo de custo deletado com sucesso']);
    }
}
