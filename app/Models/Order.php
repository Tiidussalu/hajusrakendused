<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone',
        'items', 'total', 'status', 'stripe_payment_intent',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'float',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
