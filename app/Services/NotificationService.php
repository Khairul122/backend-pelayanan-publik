<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;

class NotificationService
{
    /**
     * Send notification to specific user
     */
    public static function sendToUser($userId, $data)
    {
        return Notifikasi::createForUser($userId, $data);
    }

    /**
     * Send broadcast notification to all users
     */
    public static function broadcast($data)
    {
        return Notifikasi::broadcast($data);
    }

    /**
     * Send notification when new berita published
     */
    public static function newBeritaPublished($berita)
    {
        return self::broadcast([
            'judul' => 'Berita Baru: ' . $berita->judul,
            'isi' => 'Berita baru telah dipublikasikan. Klik untuk membaca selengkapnya.',
            'jenis' => 'info',
            'kategori' => 'berita',
            'priority' => 'normal',
            'action_url' => '/berita/' . $berita->id_berita,
            'action_text' => 'Baca Berita',
            'data' => [
                'berita_id' => $berita->id_berita,
                'kategori_id' => $berita->id_kategori,
            ],
            'created_by' => $berita->created_by,
        ]);
    }

    /**
     * Send notification when layanan status updated
     */
    public static function layananStatusUpdated($layanan)
    {
        return self::broadcast([
            'judul' => 'Update Layanan: ' . $layanan->nama_layanan,
            'isi' => 'Informasi layanan telah diperbarui. Silakan cek detail terbaru.',
            'jenis' => 'info',
            'kategori' => 'layanan',
            'priority' => 'normal',
            'action_url' => '/layanan/' . $layanan->id_layanan,
            'action_text' => 'Lihat Layanan',
            'data' => [
                'layanan_id' => $layanan->id_layanan,
                'jenis_id' => $layanan->id_jenis_layanan,
            ],
        ]);
    }

    /**
     * Send notification when saran replied by admin
     */
    public static function saranReplied($saran)
    {
        return self::sendToUser($saran->id_pengguna, [
            'judul' => 'Balasan Saran Anda',
            'isi' => 'Admin telah membalas saran Anda: "' . $saran->subjek . '"',
            'jenis' => 'sukses',
            'kategori' => 'saran',
            'priority' => 'normal',
            'action_url' => '/saran/' . $saran->id_saran,
            'action_text' => 'Lihat Balasan',
            'data' => [
                'saran_id' => $saran->id_saran,
                'status' => $saran->status,
            ],
            'created_by' => $saran->dibalas_oleh,
        ]);
    }

    /**
     * Send system maintenance notification
     */
    public static function systemMaintenance($message, $scheduledAt = null)
    {
        return self::broadcast([
            'judul' => 'Pemberitahuan Maintenance Sistem',
            'isi' => $message,
            'jenis' => 'peringatan',
            'kategori' => 'system',
            'priority' => 'high',
            'icon' => 'tools',
            'data' => [
                'maintenance_at' => $scheduledAt,
            ],
        ]);
    }

    /**
     * Send welcome notification for new user
     */
    public static function welcomeUser($userId)
    {
        return self::sendToUser($userId, [
            'judul' => 'Selamat Datang!',
            'isi' => 'Terima kasih telah bergabung dengan layanan publik online kami. Jelajahi berbagai layanan yang tersedia.',
            'jenis' => 'sukses',
            'kategori' => 'system',
            'priority' => 'normal',
            'action_url' => '/layanan',
            'action_text' => 'Lihat Layanan',
            'icon' => 'user-plus',
        ]);
    }

    /**
     * Clean expired notifications
     */
    public static function cleanExpiredNotifications()
    {
        $deleted = Notifikasi::expired()->delete();
        
        return $deleted;
    }
}