<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')
                ->constrained('committees')
                ->cascadeOnDelete();
            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('appointment_type', ['permanent', 'temporary', 'acting'])->default('permanent');
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Index untuk query riwayat aktif
            $table->index(['committee_id', 'is_active']);
            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_histories');
    }
};
