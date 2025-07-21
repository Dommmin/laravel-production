<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ImportJobEnum;
use App\Models\ImportJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportJob>
 */
class ImportJobFactory extends Factory
{
    protected $model = ImportJob::class;

    public function definition(): array
    {
        return [
            'status' => ImportJobEnum::STATUS_PENDING,
            'errors' => [],
            'total_rows' => $this->faker->numberBetween(0, 100),
            'processed_rows' => 0,
            'filename' => $this->faker->word().'.csv',
        ];
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ImportJobEnum::STATUS_COMPLETED,
                'processed_rows' => $attributes['total_rows'],
            ];
        });
    }

    public function failed(array $errors = []): self
    {
        return $this->state(function () use ($errors) {
            return [
                'status' => ImportJobEnum::STATUS_FAILED,
                'errors' => $errors ?: [
                    [
                        'row' => 1,
                        'attribute' => 'email',
                        'errors' => ['Invalid email format'],
                    ],
                ],
            ];
        });
    }
}
