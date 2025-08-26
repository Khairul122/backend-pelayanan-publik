<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Saran;
use App\Models\User;

class SaranSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user untuk testing
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => bcrypt('password123'),
            ]);
        }

        $sarans = [
            [
                'id_pengguna' => $user->id,
                'jenis_saran' => 'saran',
                'subjek' => 'Peningkatan Layanan Online',
                'isi' => 'Saya menyarankan agar layanan online dapat lebih dipercepat prosesnya dan ditambahkan fitur tracking status pengajuan.',
                'nama_pengirim' => $user->name,
                'email_pengirim' => $user->email,
                'no_hp' => '081234567890',
                'status' => 'menunggu',
                'prioritas' => 'normal',
                'is_public' => true,
                'is_anonim' => false,
            ],
            [
                'id_pengguna' => $user->id,
                'jenis_saran' => 'keluhan',
                'subjek' => 'Antrian Panjang di Loket',
                'isi' => 'Saya ingin menyampaikan keluhan mengenai antrian yang sangat panjang di loket pelayanan. Mohon dapat ditingkatkan jumlah petugas atau sistem antrian online.',
                'nama_pengirim' => $user->name,
                'email_pengirim' => $user->email,
                'status' => 'diproses',
                'prioritas' => 'tinggi',
                'is_public' => true,
                'is_anonim' => false,
                'balasan_admin' => 'Terima kasih atas keluhan Anda. Kami sedang mengevaluasi untuk menambah petugas dan mengimplementasikan sistem antrian online.',
                'tanggal_balasan' => now()->subDays(1),
                'dibalas_oleh' => 1,
            ],
            [
                'id_pengguna' => $user->id,
                'jenis_saran' => 'kritik',
                'subjek' => 'Website Sulit Diakses',
                'isi' => 'Website pelayanan publik sering mengalami error dan loading yang lambat. Perlu perbaikan infrastruktur IT.',
                'nama_pengirim' => 'Warga Peduli',
                'email_pengirim' => 'warga@email.com',
                'status' => 'selesai',
                'prioritas' => 'urgent',
                'is_public' => false,
                'is_anonim' => true,
                'balasan_admin' => 'Kritik Anda sangat berharga. Kami telah melakukan upgrade server dan optimasi website. Mohon dicoba kembali.',
                'tanggal_balasan' => now()->subHours(5),
                'dibalas_oleh' => 1,
            ],
            [
                'id_pengguna' => $user->id,
                'jenis_saran' => 'pertanyaan',
                'subjek' => 'Cara Mengurus KTP Hilang',
                'isi' => 'Bagaimana prosedur dan syarat untuk mengurus KTP yang hilang? Apakah bisa dilakukan secara online?',
                'nama_pengirim' => $user->name,
                'email_pengirim' => $user->email,
                'status' => 'selesai',
                'prioritas' => 'normal',
                'is_public' => true,
                'is_anonim' => false,
                'balasan_admin' => 'Untuk KTP hilang: 1) Lapor polisi, 2) Bawa surat keterangan hilang, KK, dan foto, 3) Datang ke Disdukcapil. Saat ini belum bisa online.',
                'tanggal_balasan' => now()->subHours(2),
                'dibalas_oleh' => 1,
            ],
            [
                'id_pengguna' => $user->id,
                'jenis_saran' => 'saran',
                'subjek' => 'Aplikasi Mobile Diperlukan',
                'isi' => 'Akan lebih baik jika ada aplikasi mobile untuk akses layanan publik yang lebih mudah dan praktis.',
                'nama_pengirim' => 'Anonim',
                'email_pengirim' => null,
                'status' => 'menunggu',
                'prioritas' => 'normal',
                'is_public' => true,
                'is_anonim' => true,
            ],
        ];

        foreach ($sarans as $saran) {
            Saran::create($saran);
        }
    }
}