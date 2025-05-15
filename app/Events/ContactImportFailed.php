<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactImportFailed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $failures = [];

    public function __construct(array $failures)
    {
        $this->failures = $failures;
    }

    public function broadcastOn()
    {
        return new Channel('imports');
    }

    public function broadcastAs(): string
    {
        return 'ContactImportFailed';
    }
}
