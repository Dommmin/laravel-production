<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ImportJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_progress_percentage(): void
    {
        $importJob = ImportJob::factory()->create([
            'total_rows' => 100,
            'processed_rows' => 25,
        ]);

        $this->assertEquals(25.0, $importJob->getProgressPercentage());
    }

    public function test_it_handles_zero_total_rows(): void
    {
        $importJob = ImportJob::factory()->create([
            'total_rows' => 0,
            'processed_rows' => 0,
        ]);

        $this->assertEquals(0.0, $importJob->getProgressPercentage());
    }

    public function test_it_detects_errors(): void
    {
        $importJob = ImportJob::factory()->failed()->create();

        $this->assertTrue($importJob->hasErrors());
    }

    public function test_it_detects_no_errors(): void
    {
        $importJob = ImportJob::factory()->create();

        $this->assertFalse($importJob->hasErrors());
    }

    public function test_it_detects_completed_status(): void
    {
        $importJob = ImportJob::factory()->completed()->create();

        $this->assertTrue($importJob->isCompleted());
        $this->assertFalse($importJob->isFailed());
        $this->assertFalse($importJob->isPending());
    }

    public function test_it_detects_failed_status(): void
    {
        $importJob = ImportJob::factory()->failed()->create();

        $this->assertTrue($importJob->isFailed());
        $this->assertFalse($importJob->isCompleted());
        $this->assertFalse($importJob->isPending());
    }

    public function test_it_detects_pending_status(): void
    {
        $importJob = ImportJob::factory()->create();

        $this->assertTrue($importJob->isPending());
        $this->assertFalse($importJob->isCompleted());
        $this->assertFalse($importJob->isFailed());
    }
}
