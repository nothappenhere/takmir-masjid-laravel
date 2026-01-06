<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 255);
            $table->string('email', 200)->unique()->nullable();
            $table->string('phone_number', 20)->unique()->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('join_date')->nullable();
            $table->enum('active_status', ['active', 'inactive', 'resigned'])->default('active');
            $table->foreignId('position_id')
                ->nullable()
                ->constrained('positions')
                ->onDelete('set null');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->string('photo_path')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexing untuk performa
            $table->index(['active_status', 'position_id']);
            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
