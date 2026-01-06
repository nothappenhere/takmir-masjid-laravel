<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('organizational_structures')
                ->onDelete('cascade');
            $table->integer('lft')->nullable();
            $table->integer('rgt')->nullable();
            $table->integer('depth')->default(0);
            $table->integer('order')->default(0);
            $table->boolean('is_division')->default(false);
            $table->string('division_name')->nullable();
            $table->text('division_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index untuk nested set
            $table->index(['lft', 'rgt']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizational_structures');
    }
};
