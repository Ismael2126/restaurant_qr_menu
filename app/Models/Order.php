<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'restaurant_table_id',
        'order_number',
        'status',
        'total_amount',
        'customer_note',
    ];

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'new' => 'New',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }

    public static function nextStatus(string $status): ?string
    {
        return match ($status) {
            'new' => 'preparing',
            'preparing' => 'ready',
            'ready' => 'completed',
            default => null,
        };
    }

    public static function nextStatusLabel(string $status): ?string
    {
        return match ($status) {
            'new' => 'Start Preparing',
            'preparing' => 'Mark Ready',
            'ready' => 'Mark Completed',
            default => null,
        };
    }
}