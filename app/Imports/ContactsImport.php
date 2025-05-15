<?php

declare(strict_types=1);

namespace App\Imports;

use App\DTO\ContactData;
use App\Enums\ImportJobEnum;
use App\Events\ContactImportError;
use App\Events\ContactImportFailed;
use App\Events\ContactImportFinished;
use App\Models\Contact;
use App\Models\ImportJob;
use App\Repositories\ImportJobRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class ContactsImport implements ShouldQueue, SkipsOnError, SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithValidation
{
    use Importable;
    use RegistersEventListeners;

    private array $failures = [];

    private ImportJob $importJob;

    public function __construct(
        string $filename,
        private readonly ImportJobRepository $importJobRepository,
    ) {
        $this->importJob = $this->importJobRepository->create($filename);
    }

    public function model(array $row): Contact
    {
        $this->importJobRepository->incrementProcessedRows($this->importJob);

        $row['phone'] = isset($row['phone']) ? (string) $row['phone'] : null;

        $contactData = ContactData::fromArray($row);

        return new Contact($contactData->toArray());
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

        $maxErrorsToShow = config('import.max_errors_to_show');
        $errorsToShow = array_slice($formattedFailures, 0, $maxErrorsToShow);

        if ($totalFailures > $maxErrorsToShow) {
            $errorsToShow[] = [
                'row' => null,
                'attribute' => null,
                'errors' => ['And '.($totalFailures - $maxErrorsToShow).' more validation errors'],
            ];
        }

        $this->importJobRepository->updateErrors($this->importJob, $errorsToShow);

        event(new ContactImportFailed($errorsToShow, $this->importJob->id));
    }

    public function onError(Throwable $e): void
    {
        Log::error('Error importing contacts', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'import_job_id' => $this->importJob->id,
        ]);

        $this->importJobRepository->updateStatus($this->importJob, ImportJobEnum::STATUS_FAILED);
        $this->importJobRepository->updateErrors($this->importJob, [[
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
        ]]);

        event(new ContactImportError($this->importJob->id));
    }

    public function chunkSize(): int
    {
        return config('import.chunk_size');
    }

    public function batchSize(): int
    {
        return config('import.chunk_size');
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $importJob = $this->importJobRepository->find($this->importJob->id);

                if (! $importJob) {
                    return;
                }

                $status = $importJob->hasErrors()
                    ? ImportJobEnum::STATUS_FAILED
                    : ImportJobEnum::STATUS_COMPLETED;

                $this->importJobRepository->updateStatus($importJob, $status);

                if ($status === ImportJobEnum::STATUS_COMPLETED) {
                    event(new ContactImportFinished($importJob->id));
                }
            },
        ];
    }
}
