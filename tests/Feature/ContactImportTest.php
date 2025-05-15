<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ImportJobEnum;
use App\Events\ContactImportError;
use App\Events\ContactImportFailed;
use App\Events\ContactImportFinished;
use App\Models\ImportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Jobs\QueueImport;
use Tests\TestCase;

class ContactImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
        Event::fake([
            ContactImportError::class,
            ContactImportFailed::class,
            ContactImportFinished::class,
        ]);
    }

    public function test_it_can_import_contacts_from_valid_file(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'contacts.csv',
            "name,email,phone,company\nJohn Doe,john@example.com,\"123456789\",ACME Inc\nJane Doe,jane@example.com,\"987654321\",XYZ Corp"
        );

        $response = $this->actingAs($user)
            ->post(route('contacts.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('import_jobs', [
            'filename' => 'contacts.csv',
        ]);

        /** @var ImportJob $importJob */
        $importJob = ImportJob::first();
        $this->assertEquals(ImportJobEnum::STATUS_PENDING, $importJob->status);

        // Process the import job
        Queue::assertPushed(QueueImport::class);
    }

    public function test_it_handles_validation_errors(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'contacts.csv',
            "name,email,phone,company\nJohn Doe,invalid-email,\"123456789\",ACME Inc"
        );

        $response = $this->actingAs($user)
            ->post(route('contacts.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Process the import job
        Queue::assertPushed(QueueImport::class);
    }

    public function test_it_validates_file_type(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('contacts.txt');

        $response = $this->actingAs($user)
            ->post(route('contacts.import'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_it_tracks_import_progress(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'contacts.csv',
            "name,email,phone,company\nJohn Doe,john@example.com,\"123456789\",ACME Inc\nJane Doe,jane@example.com,\"987654321\",XYZ Corp"
        );

        $this->actingAs($user)
            ->post(route('contacts.import'), [
                'file' => $file,
            ]);

        /** @var ImportJob $importJob */
        $importJob = ImportJob::first();
        $this->assertEquals(ImportJobEnum::STATUS_PENDING, $importJob->status);
        $this->assertEquals(0, $importJob->processed_rows);

        // Process the import job
        Queue::assertPushed(QueueImport::class);
    }
}
