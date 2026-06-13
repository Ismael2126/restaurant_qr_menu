<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    public function index()
    {
        $orders = $this->ordersQuery()->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function data()
    {
        $orders = $this->ordersQuery()->get();

        return response()->json([
            'orders' => $orders->map(function ($order) {
                return $this->transformOrder($order);
            }),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $oldStatus = $order->status;

        $validated = $request->validate([
            'status' => 'required|in:new,preparing,ready,completed,cancelled',
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        AuditHelper::log(
            'Update',
            'Order',
            'Changed order status: ' . $order->order_number .
            ' from ' . strtoupper($oldStatus) .
            ' to ' . strtoupper($order->status)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'order' => $this->transformOrder($order->fresh(['table', 'items'])),
            ]);
        }

        return back()->with('success', 'Order status updated.');
    }

    public function ticket(Order $order)
    {
        $order->load(['table', 'items']);

        return view('admin.orders.ticket', compact('order'));
    }

    private function ordersQuery()
    {
        return Order::with(['table', 'items'])
            ->orderByRaw("FIELD(status, 'new', 'preparing', 'ready', 'completed', 'cancelled')")
            ->latest();
    }

    private function transformOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => Order::statusLabel($order->status),
            'next_status' => Order::nextStatus($order->status),
            'next_status_label' => Order::nextStatusLabel($order->status),
            'table_name' => $order->table?->table_name ?? 'Unknown Table',
            'created_at' => $order->created_at->toIso8601String(),
            'created_at_label' => $order->created_at->format('d M Y - h:i A'),
            'customer_note' => $order->customer_note,
            'total_amount' => number_format($order->total_amount, 2),
            'status_url' => route('admin.orders.status', $order),
            'ticket_url' => route('admin.orders.ticket', $order),
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->unit_price, 2),
                    'line_total' => number_format($item->line_total, 2),
                ];
            })->values()->all(),
        ];
    }
}
