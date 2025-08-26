<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanans', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->string('nama_layanan');
            $table->text('deskripsi');
            $table->text('syarat_prosedur');
            $table->unsignedBigInteger('id_jenis_layanan');
            $table->string('durasi_pelayanan')->nullable(); // contoh: "3-5 hari kerja"
            $table->decimal('biaya', 10, 2)->nullable(); // biaya layanan
            $table->text('catatan_khusus')->nullable();
            $table->boolean('is_online')->default(false); // apakah bisa online
            $table->string('link_online')->nullable(); // link jika online
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_jenis_layanan')->references('id_jenis_layanan')->on('jenis_layanans')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['is_active', 'id_jenis_layanan']);
            $table->index('is_online');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanans');
    }
};