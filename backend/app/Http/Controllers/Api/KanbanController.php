<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function getOrders()
    {
        $orders = Order::with(['customer', 'status', 'items.product.images', 'items.product.mainImage', 'items.product.box', 'costs.costType', 'shipping'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status_id' => 'required|exists:order_statuses,id',
        ]);

        $order->update(['status_id' => $validated['status_id']]);

        return response()->json($order->load('status'));
    }

    public function getStatuses()
    {
        $statuses = OrderStatus::where('active', true)
            ->orderBy('order')
            ->get();

        return response()->json($statuses);
    }

    public function index()
    {
        return $this->getStatuses();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:order_statuses,slug',
            'color' => 'required|string',
            'order' => 'integer',
            'is_default' => 'boolean',
        ]);

        $status = OrderStatus::create($validated);

        return response()->json($status, 201);
    }

    public function update(Request $request, $id)
    {
        $status = OrderStatus::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:order_statuses,slug,' . $id,
            'color' => 'sometimes|string',
            'order' => 'integer',
            'is_default' => 'boolean',
        ]);

        $status->update($validated);

        return response()->json($status);
    }

    public function destroy($id)
    {
        $status = OrderStatus::findOrFail($id);
        $status->delete();

        return response()->json(['message' => 'Status deleted successfully']);
    }
}
