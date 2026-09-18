<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_group_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duplicate_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('image_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_group_images');
    }
};
