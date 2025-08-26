<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriBerita;
use App\Models\Berita;

class BeritaSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            ['nama_kategori' => 'Pengumuman', 'deskripsi' => 'Pengumuman resmi'],
            ['nama_kategori' => 'Berita Terkini', 'deskripsi' => 'Berita terbaru'],
            ['nama_kategori' => 'Layanan Publik', 'deskripsi' => 'Info layanan publik'],
        ];

        foreach ($categories as $category) {
            KategoriBerita::create($category);
        }

        // Create sample berita
        $beritas = [
            [
                'judul' => 'Pengumuman Layanan Baru',
                'isi' => 'Kami dengan bangga mengumumkan layanan baru...',
                'id_kategori' => 1,
                'is_published' => true,
                'published_at' => now(),
                'created_by' => 1,
            ],
            [
                'judul' => 'Update Sistem Pelayanan',
                'isi' => 'Sistem pelayanan akan diupdate untuk meningkatkan...',
                'id_kategori' => 2,
                'is_published' => true,
                'published_at' => now(),
                'created_by' => 1,
            ],
        ];

        foreach ($beritas as $berita) {
            Berita::create($berita);
        }
    }
}