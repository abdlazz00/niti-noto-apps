<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenNewOrder implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('kitchen')];
    }

    public function broadcastWith(): array
    {
        $this->order->loadMissing('table', 'items.menuItem');

        return [
            'id'           => $this->order->id,
            'order_number' => $this->order->order_number,
            'table_number' => $this->order->table->number,
            'table_name'   => $this->order->table->name,
            'notes'        => $this->order->notes,
            'items'        => $this->order->items->map(fn ($i) => [
                'name'  => $i->menuItem->name,
                'qty'   => $i->qty,
                'notes' => $i->notes,
            ])->all(),
        ];
    }
}
