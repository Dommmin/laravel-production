<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportJobEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'errors',
        'total_rows',
        'processed_rows',
        'filename',
    ];

    protected $casts = [
        'errors' => 'array',
        'status' => ImportJobEnum::class,
    ];

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_rows === 0) {
            return 0.0;
        }

        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === ImportJobEnum::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === ImportJobEnum::STATUS_FAILED;
    }

    public function isPending(): bool
    {
        return $this->status === ImportJobEnum::STATUS_PENDING;
    }
}
