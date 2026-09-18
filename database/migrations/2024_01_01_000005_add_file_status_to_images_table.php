<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->boolean('file_exists')->nullable()->after('status');
            $table->timestamp('file_checked_at')->nullable()->after('file_exists');
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['file_exists', 'file_checked_at']);
        });
    }
};
