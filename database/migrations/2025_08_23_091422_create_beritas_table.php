<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beritas', function (Blueprint $table) {
            $table->id('id_berita');
            $table->string('judul');
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->unsignedBigInteger('id_kategori');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_kategori')
                  ->references('id_kategori')
                  ->on('kategori_beritas')
                  ->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Indexes
            $table->index(['is_published', 'published_at']);
            $table->index('id_kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beritas');
    }
};
