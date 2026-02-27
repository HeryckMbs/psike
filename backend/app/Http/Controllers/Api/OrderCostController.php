<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCost;
use Illuminate\Http\Request;

class OrderCostController extends Controller
{
    public function index($orderId)
    {
        $order = Order::findOrFail($orderId);
        $costs = $order->costs()->with('costType')->orderBy('created_at', 'desc')->get();

        return response()->json($costs);
    }

    public function store(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'cost_type_id' => 'required|exists:cost_types,id',
            'value' => 'required|numeric|min:0',
        ]);

        $cost = $order->costs()->create($validated);

        return response()->json($cost->load('costType'), 201);
    }

    public function update(Request $request, $orderId, $costId)
    {
        $order = Order::findOrFail($orderId);
        $cost = $order->costs()->findOrFail($costId);

        $validated = $request->validate([
            'cost_type_id' => 'required|exists:cost_types,id',
            'value' => 'required|numeric|min:0',
        ]);

        $cost->update($validated);

        return response()->json($cost->load('costType'));
    }

    public function destroy($orderId, $costId)
    {
        $order = Order::findOrFail($orderId);
        $cost = $order->costs()->findOrFail($costId);

        $cost->delete();

        return response()->json(['message' => 'Custo deletado com sucesso']);
    }
}
