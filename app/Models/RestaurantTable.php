<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $fillable = [
        'table_name',
        'table_code',
        'qr_token',
        'is_active',
    ];
}