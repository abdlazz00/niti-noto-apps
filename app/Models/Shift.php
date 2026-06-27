<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = ['cashier_id', 'started_at', 'ended_at', 'total_revenue'];

    protected $casts = [
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
        'total_revenue' => 'decimal:2',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
