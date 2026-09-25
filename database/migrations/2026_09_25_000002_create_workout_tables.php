<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('muscle_group', 20)->index();
            $table->string('equipment', 20)->index();
            $table->text('instructions')->nullable();
            $table->string('video_url')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedTinyInteger('default_sets')->default(3);
            $table->string('default_reps', 20)->default('10-12');
            $table->unsignedSmallInteger('default_rest_seconds')->default(60);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // user_id NULL = plantilla reutilizable.
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('workout_plans')->nullOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('level', 20)->default('beginner');
            $table->string('goal', 30)->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'active']);
        });

        Schema::create('workout_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->unsignedTinyInteger('weekday')->nullable()->comment('1 = lunes … 7 = domingo; NULL = día libre');
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('workout_day_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('sets')->default(3);
            $table->string('reps', 20)->default('10-12');
            $table->unsignedSmallInteger('rest_seconds')->default(60);
            $table->string('notes')->nullable();
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        // Lo que el cliente marca como hecho en la app.
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_day_id')->nullable()->constrained()->nullOnDelete();
            $table->date('performed_on');
            $table->json('completed_exercise_ids')->nullable();
            $table->unsignedTinyInteger('effort')->nullable()->comment('1-5');
            $table->string('notes')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'workout_day_id', 'performed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
        Schema::dropIfExists('workout_day_exercises');
        Schema::dropIfExists('workout_days');
        Schema::dropIfExists('workout_plans');
        Schema::dropIfExists('exercises');
    }
};
