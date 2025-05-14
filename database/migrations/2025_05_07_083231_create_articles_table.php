<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->string('slug')->unique();
            $blueprint->text('content');
            $blueprint->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $blueprint->json('location')->nullable();
            $blueprint->string('city_name')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
