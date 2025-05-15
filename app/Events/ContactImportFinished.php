<?php

declare(strict_types=1);

namespace App\Events;

use App\Events\Contracts\ImportEvent;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactImportFinished implements ImportEvent, ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        private readonly int $importJobId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('imports');
    }

    public function broadcastAs(): string
    {
        return 'ContactImportFinished';
    }

    public function getImportJobId(): int
    {
        return $this->importJobId;
    }

    public function getTimestamp(): Carbon
    {
        return now();
    }
}
