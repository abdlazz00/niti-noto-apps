<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    protected $fillable = ['user_id', 'phone', 'address', 'join_date', 'photo', 'notes'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
