<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Saran extends Model
{
    use HasFactory;

    protected $table = 'sarans';
    protected $primaryKey = 'id_saran';

    protected $fillable = [
        'id_pengguna',
        'jenis_saran',
        'subjek',
        'isi',
        'nama_pengirim',
        'email_pengirim',
        'no_hp',
        'status',
        'balasan_admin',
        'tanggal_balasan',
        'dibalas_oleh',
        'prioritas',
        'is_public',
        'is_anonim',
        'lampiran',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_anonim' => 'boolean',
        'lampiran' => 'array',
        'tanggal_balasan' => 'datetime',
    ];

    // Relationships
    public function pengguna()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function adminPembalas()
    {
        return $this->belongsTo(User::class, 'dibalas_oleh');
    }

    // Scopes
    public function scopeByStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByJenis(Builder $query, $jenis)
    {
        return $query->where('jenis_saran', $jenis);
    }

    public function scopeByPrioritas(Builder $query, $prioritas)
    {
        return $query->where('prioritas', $prioritas);
    }

    public function scopePublic(Builder $query)
    {
        return $query->where('is_public', true);
    }

    public function scopeMenunggu(Builder $query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('subjek', 'like', "%{$search}%")
              ->orWhere('isi', 'like', "%{$search}%")
              ->orWhere('nama_pengirim', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'menunggu' => ['class' => 'warning', 'text' => 'Menunggu'],
            'diproses' => ['class' => 'info', 'text' => 'Diproses'],
            'selesai' => ['class' => 'success', 'text' => 'Selesai'],
            'ditolak' => ['class' => 'danger', 'text' => 'Ditolak'],
        ];

        return $badges[$this->status] ?? ['class' => 'secondary', 'text' => 'Unknown'];
    }

    public function getPrioritasBadgeAttribute()
    {
        $badges = [
            'rendah' => ['class' => 'success', 'text' => 'Rendah'],
            'normal' => ['class' => 'primary', 'text' => 'Normal'],
            'tinggi' => ['class' => 'warning', 'text' => 'Tinggi'],
            'urgent' => ['class' => 'danger', 'text' => 'Urgent'],
        ];

        return $badges[$this->prioritas] ?? ['class' => 'secondary', 'text' => 'Normal'];
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
}