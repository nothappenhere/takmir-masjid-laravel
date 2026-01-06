<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')
                ->constrained('committees')
                ->cascadeOnDelete();
            $table->date('duty_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location', 200);
            $table->enum('duty_type', ['piket', 'kebersihan', 'keamanan', 'administrasi', 'lainnya'])->default('piket');
            $table->enum('status', ['pending', 'ongoing', 'completed', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_type', ['daily', 'weekly', 'monthly'])->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexing untuk query jadwal
            $table->index(['duty_date', 'duty_type']);
            $table->index(['committee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_schedules');
    }
};
