<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ImportJobEnum;
use App\Models\ImportJob;

class ImportJobRepository
{
    public function create(string $filename): ImportJob
    {
        return ImportJob::create([
            'filename' => $filename,
            'errors' => [],
            'status' => ImportJobEnum::STATUS_PENDING->value,
        ]);
    }

    public function incrementProcessedRows(ImportJob $importJob): void
    {
        $importJob->increment('processed_rows');
    }

    public function updateErrors(ImportJob $importJob, array $errors): void
    {
        $importJob->update([
            'errors' => array_merge($importJob->errors ?? [], $errors),
        ]);
    }

    public function updateStatus(ImportJob $importJob, ImportJobEnum $status): void
    {
        $importJob->update(['status' => $status->value]);
    }

    public function find(int $id): ?ImportJob
    {
        return ImportJob::find($id);
    }
}
