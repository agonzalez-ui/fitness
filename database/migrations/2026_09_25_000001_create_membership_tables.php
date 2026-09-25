<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->unsignedSmallInteger('duration_months');
            $table->decimal('price', 10, 2);
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->index();
            $table->decimal('price', 10, 2);
            $table->string('payment_method', 20);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('plans');
    }
};
