<?php

// Update Model Notifikasi - app/Models/Notifikasi.php
// Tambahkan default parameter untuk scope methods

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasis';
    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'id_pengguna',
        'judul',
        'isi',
        'jenis',
        'kategori',
        'icon',
        'action_url',
        'action_text',
        'data',
        'is_read',
        'is_broadcast',
        'priority',
        'read_at',
        'expired_at',
        'created_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_broadcast' => 'boolean',
        'data' => 'array',
        'read_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    // Relationships
    public function pengguna()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes - FIXED: Tambahkan default parameter
    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('id_pengguna', $userId)
              ->orWhere('is_broadcast', true);
        });
    }

    public function scopeUnread(Builder $query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByJenis(Builder $query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeByKategori(Builder $query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeByPriority(Builder $query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where(function($q) {
            $q->whereNull('expired_at')
              ->orWhere('expired_at', '>', now());
        });
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('expired_at', '<=', now());
    }

    // FIXED: Tambahkan default parameter untuk broadcast()
    public function scopeBroadcast(Builder $query)
    {
        return $query->where('is_broadcast', true);
    }

    // FIXED: Tambahkan default parameter untuk recent()
    public function scopeRecent(Builder $query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getJenisBadgeAttribute()
    {
        $badges = [
            'info' => ['class' => 'info', 'text' => 'Info', 'icon' => 'info-circle'],
            'peringatan' => ['class' => 'warning', 'text' => 'Peringatan', 'icon' => 'exclamation-triangle'],
            'sukses' => ['class' => 'success', 'text' => 'Sukses', 'icon' => 'check-circle'],
            'error' => ['class' => 'danger', 'text' => 'Error', 'icon' => 'times-circle'],
            'pengumuman' => ['class' => 'primary', 'text' => 'Pengumuman', 'icon' => 'bullhorn'],
        ];

        return $badges[$this->jenis] ?? ['class' => 'secondary', 'text' => 'Info', 'icon' => 'info'];
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => ['class' => 'success', 'text' => 'Rendah'],
            'normal' => ['class' => 'primary', 'text' => 'Normal'],
            'high' => ['class' => 'warning', 'text' => 'Tinggi'],
            'urgent' => ['class' => 'danger', 'text' => 'Urgent'],
        ];

        return $badges[$this->priority] ?? ['class' => 'primary', 'text' => 'Normal'];
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIsExpiredAttribute()
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function getHasActionAttribute()
    {
        return !empty($this->action_url) && !empty($this->action_text);
    }

    public function getNamaPengirimDisplayAttribute()
    {
        if ($this->is_anonim) {
            return 'Anonim';
        }
        
        return $this->pengguna?->name ?? $this->nama_pengirim;
    }

    public function getLampiranUrlsAttribute()
    {
        if (!$this->lampiran) {
            return [];
        }

        return collect($this->lampiran)->map(function ($file) {
            return url('storage/saran/' . $file);
        })->toArray();
    }

    // Methods
    public function markAsRead($userId = null)
    {
        if ($userId && $this->id_pengguna != $userId && !$this->is_broadcast) {
            return false;
        }

        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return true;
    }

    public function isReadableBy($userId)
    {
        return $this->id_pengguna == $userId || $this->is_broadcast;
    }

    public static function createForUser($userId, $data)
    {
        return self::create(array_merge($data, [
            'id_pengguna' => $userId,
            'is_broadcast' => false,
        ]));
    }

    public static function broadcast($data)
    {
        return self::create(array_merge($data, [
            'id_pengguna' => null,
            'is_broadcast' => true,
        ]));
    }
}