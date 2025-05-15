<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\ImportJobEnum;
use App\Events\ContactImportError;
use App\Events\ContactImportFailed;
use App\Events\ContactImportFinished;
use App\Models\Contact;
use App\Models\ImportJob;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactsImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnFailure, SkipsOnError, ShouldQueue, WithEvents, WithBatchInserts
{
    use Importable, RegistersEventListeners;

    private array $failures = [];
    private ImportJob $importJob;
    private const MAX_ERRORS_TO_SHOW = 3;

    public function __construct(string $filename)
    {
        $this->importJob = ImportJob::create([
            'filename' => $filename,
            'errors' => [],
        ]);
    }

    public function model(array $row): Contact
    {
        $this->importJob->increment('processed_rows');

        return new Contact([
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'company' => $row['company'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable'],
            'company' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        $this->failures = array_merge($this->failures, $failures);
        $totalFailures = count($this->failures);

        $formattedFailures = array_map(function (Failure $failure) {
            return [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
            ];
        }, $failures);

        $errorsToShow = array_slice($formattedFailures, 0, self::MAX_ERRORS_TO_SHOW);

        if ($totalFailures > self::MAX_ERRORS_TO_SHOW) {
            $errorsToShow[] = [
                'row' => null,
                'attribute' => null,
                'errors' => ['And ' . ($totalFailures - self::MAX_ERRORS_TO_SHOW) . ' more validation errors'],
            ];
        }

        $this->importJob->update([
            'errors' => array_merge($this->importJob->errors ?? [], $errorsToShow),
        ]);

        event(new ContactImportFailed($errorsToShow));
    }

    public function onError(Throwable $e): void
    {
        Log::info('Error importing contacts', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
        ]);

        $this->importJob->update([
            'status' => ImportJobEnum::STATUS_FAILED->value,
            'errors' => array_merge($this->importJob->errors ?? [], [[
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]]),
        ]);

        event(new ContactImportError());
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                $importJob = ImportJob::find($this->importJob->id);
                if (!$importJob) return;

                $status = empty($importJob->hasErrors())
                    ? ImportJobEnum::STATUS_COMPLETED->value
                    : ImportJobEnum::STATUS_FAILED->value;

                $importJob->update(['status' => $status]);

                if ($status === ImportJobEnum::STATUS_COMPLETED->value) {
                    event(new ContactImportFinished());
                }
            },
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
