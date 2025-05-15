<?php

namespace App\Enums;

enum ImportJobEnum: string
{
    case STATUS_PENDING = 'pending';
    case STATUS_COMPLETED = 'completed';
    case STATUS_FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
        };
    }

    public static function values(): array
    {
        return [
            self::STATUS_PENDING->value,
            self::STATUS_COMPLETED->value,
            self::STATUS_FAILED->value,
        ];
    }
}
