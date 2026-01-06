<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnDelete();
            $table->string('task_name', 200);
            $table->text('task_description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('estimated_hours')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly', 'as_needed'])->default('as_needed');
            $table->boolean('is_core_responsibility')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index untuk filter
            $table->index(['position_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_responsibilities');
    }
};
