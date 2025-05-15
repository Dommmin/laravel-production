<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ImportJobEnum;
use App\Models\ImportJob;
use App\Repositories\ImportJobRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportJobRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ImportJobRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ImportJobRepository;
    }

    public function test_it_creates_import_job(): void
    {
        $importJob = $this->repository->create('test.csv');

        $this->assertInstanceOf(ImportJob::class, $importJob);
        $this->assertEquals('test.csv', $importJob->filename);
        $this->assertEquals(ImportJobEnum::STATUS_PENDING, $importJob->status);
        $this->assertEmpty($importJob->errors);
    }

    public function test_it_increments_processed_rows(): void
    {
        $importJob = ImportJob::factory()->create([
            'processed_rows' => 5,
        ]);

        $this->repository->incrementProcessedRows($importJob);

        $this->assertEquals(6, $importJob->fresh()->processed_rows);
    }

    public function test_it_updates_errors(): void
    {
        $importJob = ImportJob::factory()->create([
            'errors' => [],
        ]);

        $errors = [
            ['row' => 1, 'message' => 'Invalid email'],
        ];

        $this->repository->updateErrors($importJob, $errors);

        $this->assertEquals($errors, $importJob->fresh()->errors);
    }

    public function test_it_merges_errors(): void
    {
        $importJob = ImportJob::factory()->create([
            'errors' => [
                ['row' => 1, 'message' => 'Invalid email'],
            ],
        ]);

        $newErrors = [
            ['row' => 2, 'message' => 'Invalid phone'],
        ];

        $this->repository->updateErrors($importJob, $newErrors);

        $this->assertCount(2, $importJob->fresh()->errors);
    }

    public function test_it_updates_status(): void
    {
        $importJob = ImportJob::factory()->create([
            'status' => ImportJobEnum::STATUS_PENDING,
        ]);

        $this->repository->updateStatus($importJob, ImportJobEnum::STATUS_COMPLETED);

        $this->assertEquals(ImportJobEnum::STATUS_COMPLETED, $importJob->fresh()->status);
    }

    public function test_it_finds_import_job(): void
    {
        $importJob = ImportJob::factory()->create();

        $found = $this->repository->find($importJob->id);

        $this->assertInstanceOf(ImportJob::class, $found);
        $this->assertEquals($importJob->id, $found->id);
    }

    public function test_it_returns_null_for_nonexistent_import_job(): void
    {
        $found = $this->repository->find(999);

        $this->assertNull($found);
    }
}
