<?php

namespace App\Events;

use App\Models\Expense;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Expense $expense) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('owner')];
    }

    public function broadcastWith(): array
    {
        $this->expense->loadMissing('category', 'createdBy');

        return [
            'id'                 => $this->expense->id,
            'title'              => $this->expense->title,
            'amount'             => (float) $this->expense->amount,
            'category_name'      => $this->expense->category->name,
            'submitted_by_name'  => $this->expense->createdBy->name,
        ];
    }
}
