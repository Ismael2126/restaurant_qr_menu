<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'is_active',
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('name');
    }
}