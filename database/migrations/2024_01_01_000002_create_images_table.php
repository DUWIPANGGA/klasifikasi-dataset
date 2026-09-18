<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('filepath');
            $table->text('image_url')->nullable();
            $table->text('thumbnail')->nullable();
            $table->text('source')->nullable();
            $table->text('title')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('hash')->index();
            $table->timestamp('downloaded_at')->nullable();
            $table->string('fish_name');
            $table->string('common_name')->nullable();
            $table->string('label')->index();
            $table->text('query')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
