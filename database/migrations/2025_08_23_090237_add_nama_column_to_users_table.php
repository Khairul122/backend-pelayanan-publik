<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom nama setelah id
            $table->string('nama')->after('id');
            
            // Drop kolom name jika ada
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan kolom name
            $table->string('name')->after('id');
            
            // Drop kolom nama
            if (Schema::hasColumn('users', 'nama')) {
                $table->dropColumn('nama');
            }
        });
    }
};