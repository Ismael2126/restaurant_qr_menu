<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicMenuController extends Controller
{
    public function show($token)
    {
        $table = RestaurantTable::where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $categories = Category::where('is_active', true)
            ->with(['menuItems' => function ($query) {
                $query->where('is_available', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.menu', compact('table', 'categories'));
    }

    public function storeOrder(Request $request, $token)
    {
        $table = RestaurantTable::where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1|max:50',
            'customer_note' => 'nullable|string|max:1000',
        ]);

        $order = DB::transaction(function () use ($validated, $table) {
            $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999);

            $order = Order::create([
                'restaurant_table_id' => $table->id,
                'order_number' => $orderNumber,
                'status' => 'new',
                'total_amount' => 0,
                'customer_note' => $validated['customer_note'] ?? null,
            ]);

            $total = 0;

            foreach ($validated['items'] as $cartItem) {
                $menuItem = MenuItem::where('id', $cartItem['id'])
                    ->where('is_available', true)
                    ->firstOrFail();

                $quantity = (int) $cartItem['quantity'];
                $lineTotal = $menuItem->price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'unit_price' => $menuItem->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            $order->update([
                'total_amount' => $total,
            ]);

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Order sent successfully.',
            'order_number' => $order->order_number,
        ]);
    }
}