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

    private int $importJobId;

    public function __construct(array $failures, int $importJobId)
    {
        $this->failures = $failures;
        $this->importJobId = $importJobId;
    }

    public function broadcastOn()
    {
        return new Channel('imports');
    }

    public function broadcastAs(): string
    {
        return 'ContactImportFailed';
    }

    public function broadcastWith(): array
    {
        return [
            'failures' => $this->failures,
            'importJobId' => $this->importJobId,
        ];
    }
}
