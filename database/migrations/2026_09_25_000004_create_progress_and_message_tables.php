<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('recorded_on');
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('body_fat_pct', 4, 1)->nullable();
            $table->decimal('waist_cm', 5, 1)->nullable();
            $table->decimal('hips_cm', 5, 1)->nullable();
            $table->decimal('chest_cm', 5, 1)->nullable();
            $table->decimal('arms_cm', 5, 1)->nullable();
            $table->decimal('legs_cm', 5, 1)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'recorded_on']);
        });

        Schema::create('progress_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('pose', 10)->default('front')->comment('front, side, back');
            $table->date('taken_on');
            $table->timestamps();

            $table->index(['user_id', 'taken_on']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'read_at']);
            $table->index(['sender_id', 'recipient_id']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 20);
            $table->morphs('subject');
            $table->string('description');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('progress_photos');
        Schema::dropIfExists('progress_entries');
    }
};
