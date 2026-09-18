<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained()->cascadeOnDelete();
            $table->string('hash');
            $table->string('type')->default('internal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_groups');
    }
};
