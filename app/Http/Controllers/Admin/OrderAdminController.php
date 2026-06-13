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
        $orders = Order::with(['table', 'items'])
            ->orderByRaw("FIELD(status, 'new', 'preparing', 'ready', 'completed', 'cancelled')")
            ->latest()
            ->get();

        return view('admin.orders.index', compact('orders'));
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

        return back()->with('success', 'Order status updated.');
    }
}