<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('storage_driver')->default('local');
            $table->string('root_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
