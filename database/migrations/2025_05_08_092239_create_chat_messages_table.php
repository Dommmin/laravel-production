<?php

declare(strict_types=1);

use App\Models\Chat;
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
        Schema::create('chats', function (Blueprint $blueprint): void {
            $blueprint->string('id')->primary(); // uuid
            $blueprint->string('name')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('chat_id');
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->text('message');
            $blueprint->timestamp('read_at')->nullable();
            $blueprint->timestamps();

            $blueprint->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('chat_users', function (Blueprint $blueprint): void {
            $blueprint->string('chat_id');
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->primary(['chat_id', 'user_id']);
            $blueprint->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('chat_message_reads', function (Blueprint $blueprint): void {
            $blueprint->unsignedBigInteger('chat_message_id');
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->timestamps();
            $blueprint->primary(['chat_message_id', 'user_id']);
            $blueprint->foreign('chat_message_id')->references('id')->on('chat_messages')->onDelete('cascade');
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $admin = User::create([
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'password' => bcrypt('Pa$$w0rd!')
        ]);

        $user = User::create([
            'email' => 'user@example.com',
            'name' => 'User',
            'password' => bcrypt('Pa$$w0rd!')
        ]);

        $chat = Chat::create();
        $chat->users()->attach([$admin->id, $user->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_users');
        Schema::dropIfExists('chat_message_reads');
    }
};
