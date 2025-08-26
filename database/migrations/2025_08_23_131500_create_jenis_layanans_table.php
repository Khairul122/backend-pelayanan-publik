<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_layanans', function (Blueprint $table) {
            $table->id('id_jenis_layanan');
            $table->string('nama_jenis');
            $table->text('deskripsi')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_layanans');
    }
};