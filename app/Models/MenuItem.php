<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = ['category_id', 'name', 'price', 'image', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
