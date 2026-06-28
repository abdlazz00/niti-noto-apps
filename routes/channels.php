<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Cashier receives new order notifications
Broadcast::channel('cashier', function ($user) {
    return $user?->hasRole('cashier') ?? false;
});

// Kitchen (staff) receives order confirmation to start preparing
Broadcast::channel('kitchen', function ($user) {
    return $user?->hasRole('staff') ?? false;
});

// Owner receives expense notifications
Broadcast::channel('owner', function ($user) {
    return $user?->hasRole('owner') ?? false;
});

// Public channels 'order.{id}' and 'orders' need no registration
