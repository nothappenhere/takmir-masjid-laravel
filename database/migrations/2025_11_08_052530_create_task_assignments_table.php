<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')
                ->constrained('committees')
                ->cascadeOnDelete();
            $table->foreignId('job_responsibility_id')
                ->constrained('job_responsibilities')
                ->cascadeOnDelete();
            $table->date('assigned_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue', 'cancelled'])->default('pending');
            $table->integer('progress_percentage')->default(0);
            $table->text('notes')->nullable();
            $table->date('completed_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('committees');
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Index untuk query tugas
            $table->index(['committee_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
