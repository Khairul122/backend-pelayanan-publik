<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->unsignedBigInteger('id_pengguna')->nullable(); // null = broadcast to all
            $table->string('judul');
            $table->text('isi');
            $table->enum('jenis', ['info', 'peringatan', 'sukses', 'error', 'pengumuman'])->default('info');
            $table->enum('kategori', ['system', 'layanan', 'berita', 'saran', 'custom'])->default('system');
            $table->string('icon')->nullable();
            $table->string('action_url')->nullable(); // URL untuk action button
            $table->string('action_text')->nullable(); // Text untuk action button
            $table->json('data')->nullable(); // Additional data as JSON
            $table->boolean('is_read')->default(false);
            $table->boolean('is_broadcast')->default(false); // untuk semua user
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expired_at')->nullable(); // notifikasi bisa expire
            $table->unsignedBigInteger('created_by')->nullable(); // admin yang buat
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pengguna')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['id_pengguna', 'is_read']);
            $table->index(['is_broadcast', 'created_at']);
            $table->index(['jenis', 'kategori']);
            $table->index('priority');
            $table->index('expired_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};