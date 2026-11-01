<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', fn (Blueprint $table) => $table->mediumText('key_info')->nullable()->change());
    }

    public function down(): void
    {
        if (DB::table('reports')->whereRaw('CHAR_LENGTH(key_info) > 1000')->exists()) {
            throw new RuntimeException('Cannot shrink report segments: saved content exceeds 1,000 characters.');
        }
        Schema::table('reports', fn (Blueprint $table) => $table->string('key_info', 1000)->nullable()->change());
    }
};
