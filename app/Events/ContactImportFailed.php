<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Validators\Failure;

class ContactImportFailed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $failures = [];

    public function __construct(array $failures)
    {
        $this->failures = array_map(function (Failure $failure) {
            return [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
            ];
        }, $failures);
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
