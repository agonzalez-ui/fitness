<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Valores por porción (portion_label), no por 100 g, para que el entrenador piense en "1 taza", "1 huevo"...
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('category', 20)->index();
            $table->string('portion_label', 40);
            $table->unsignedSmallInteger('calories');
            $table->decimal('protein_g', 5, 1)->default(0);
            $table->decimal('carbs_g', 5, 1)->default(0);
            $table->decimal('fats_g', 5, 1)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('nutrition_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('nutrition_plans')->nullOnDelete();
            $table->string('title', 150);
            $table->string('goal', 30)->nullable();
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'active']);
        });

        Schema::create('nutrition_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nutrition_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->time('time')->nullable();
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('nutrition_meal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nutrition_meal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->restrictOnDelete();
            $table->decimal('servings', 4, 2)->default(1);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_meal_items');
        Schema::dropIfExists('nutrition_meals');
        Schema::dropIfExists('nutrition_plans');
        Schema::dropIfExists('foods');
    }
};
