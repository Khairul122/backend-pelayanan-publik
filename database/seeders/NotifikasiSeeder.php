<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notifikasi;
use App\Models\User;

class NotifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $notifications = [
            // Broadcast notifications
            [
                'id_pengguna' => null,
                'judul' => 'Sistem Layanan Online Telah Aktif',
                'isi' => 'Selamat datang! Sistem layanan publik online telah resmi beroperasi. Nikmati kemudahan akses layanan 24/7.',
                'jenis' => 'pengumuman',
                'kategori' => 'system',
                'priority' => 'high',
                'is_broadcast' => true,
                'icon' => 'bullhorn',
                'action_url' => '/layanan',
                'action_text' => 'Lihat Layanan',
                'created_by' => 1,
            ],
            [
                'id_pengguna' => null,
                'judul' => 'Update Fitur Pencarian',
                'isi' => 'Fitur pencarian telah ditingkatkan untuk memudahkan Anda menemukan layanan yang dibutuhkan.',
                'jenis' => 'info',
                'kategori' => 'system',
                'priority' => 'normal',
                'is_broadcast' => true,
                'icon' => 'search',
                'created_by' => 1,
            ],
            
            // Personal notifications
            [
                'id_pengguna' => $user->id,
                'judul' => 'Selamat Datang ' . $user->name,
                'isi' => 'Terima kasih telah mendaftar. Jelajahi berbagai layanan publik yang tersedia untuk Anda.',
                'jenis' => 'sukses',
                'kategori' => 'system',
                'priority' => 'normal',
                'is_broadcast' => false,
                'icon' => 'user-check',
                'action_url' => '/profile',
                'action_text' => 'Lengkapi Profile',
                'created_by' => 1,
            ],
            [
                'id_pengguna' => $user->id,
                'judul' => 'Saran Anda Telah Dibalas',
                'isi' => 'Admin telah memberikan tanggapan atas saran yang Anda kirimkan. Silakan cek balasannya.',
                'jenis' => 'sukses',
                'kategori' => 'saran',
                'priority' => 'normal',
                'is_broadcast' => false,
                'icon' => 'reply',
                'action_url' => '/my-saran',
                'action_text' => 'Lihat Balasan',
                'is_read' => false,
                'created_by' => 1,
            ],
            [
                'id_pengguna' => $user->id,
                'judul' => 'Berita Baru: Update Layanan KTP',
                'isi' => 'Informasi terbaru mengenai proses pengurusan KTP elektronik telah tersedia.',
                'jenis' => 'info',
                'kategori' => 'berita',
                'priority' => 'normal',
                'is_broadcast' => false,
                'action_url' => '/berita',
                'action_text' => 'Baca Berita',
                'created_by' => 1,
            ],
            
            // High priority notifications
            [
                'id_pengguna' => null,
                'judul' => 'Maintenance Sistem Terjadwal',
                'isi' => 'Sistem akan mengalami maintenance pada tanggal 25 Agustus 2025 pukul 01:00 - 05:00 WIB.',
                'jenis' => 'peringatan',
                'kategori' => 'system',
                'priority' => 'urgent',
                'is_broadcast' => true,
                'icon' => 'tools',
                'expired_at' => now()->addDays(2),
                'created_by' => 1,
            ],
        ];

        foreach ($notifications as $notification) {
            Notifikasi::create($notification);
        }
    }
}