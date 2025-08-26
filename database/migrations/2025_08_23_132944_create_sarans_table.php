<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sarans', function (Blueprint $table) {
            $table->id('id_saran');
            $table->unsignedBigInteger('id_pengguna');
            $table->enum('jenis_saran', ['saran', 'keluhan', 'kritik', 'pertanyaan'])->default('saran');
            $table->string('subjek');
            $table->text('isi');
            $table->string('nama_pengirim')->nullable();
            $table->string('email_pengirim')->nullable();
            $table->string('no_hp')->nullable();
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'ditolak'])->default('menunggu');
            $table->text('balasan_admin')->nullable();
            $table->timestamp('tanggal_balasan')->nullable();
            $table->unsignedBigInteger('dibalas_oleh')->nullable();
            $table->enum('prioritas', ['rendah', 'normal', 'tinggi', 'urgent'])->default('normal');
            $table->boolean('is_public')->default(false); // apakah bisa dilihat publik
            $table->boolean('is_anonim')->default(false);
            $table->json('lampiran')->nullable(); // untuk multiple files
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pengguna')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dibalas_oleh')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['jenis_saran', 'status']);
            $table->index('prioritas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarans');
    }
};