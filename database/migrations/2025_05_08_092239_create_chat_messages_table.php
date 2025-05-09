<?php

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
        Schema::create('chats', function (Blueprint $table) {
            $table->string('id')->primary(); // uuid
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->unsignedBigInteger('user_id');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('chat_users', function (Blueprint $table) {
            $table->string('chat_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['chat_id', 'user_id']);
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->primary(['chat_message_id', 'user_id']);
            $table->foreign('chat_message_id')->references('id')->on('chat_messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
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
