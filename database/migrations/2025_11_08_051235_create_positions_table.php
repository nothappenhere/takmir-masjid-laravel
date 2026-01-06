<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('positions')
                ->onDelete('set null');
            $table->integer('order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('level', ['leadership', 'division_head', 'staff', 'volunteer'])->default('staff');
            $table->timestamps();
            $table->softDeletes();

            // Indexing untuk performa
            $table->index(['status', 'level']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
