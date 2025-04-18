<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Row;

class RowImported implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Row $row;

    public function __construct(Row $row)
    {
        $this->row = $row;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('imports');
    }

    public function broadcastAs(): string
    {
        return 'row.imported';
    }
}
