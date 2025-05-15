<?php

declare(strict_types=1);

namespace App\Events\Contracts;

use Carbon\Carbon;

interface ImportEvent
{
    public function getImportJobId(): int;

    public function getTimestamp(): Carbon;
}
