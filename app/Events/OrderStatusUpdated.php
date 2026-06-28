<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $newStatus,
    ) {}

    public function broadcastOn(): array
    {
        // Public channel so guest customers can subscribe without auth
        return [new Channel('order.' . $this->order->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status'   => $this->newStatus,
        ];
    }
}
