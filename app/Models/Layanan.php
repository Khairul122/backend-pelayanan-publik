<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanans';
    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        'nama_layanan',
        'deskripsi',
        'syarat_prosedur',
        'id_jenis_layanan',
        'durasi_pelayanan',
        'biaya',
        'catatan_khusus',
        'is_online',
        'link_online',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'biaya' => 'decimal:2',
    ];

    // Relationships
    public function jenisLayanan()
    {
        return $this->belongsTo(JenisLayanan::class, 'id_jenis_layanan', 'id_jenis_layanan');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnline(Builder $query)
    {
        return $query->where('is_online', true);
    }

    public function scopeByJenis(Builder $query, $jenisId)
    {
        return $query->where('id_jenis_layanan', $jenisId);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama_layanan', 'like', "%{$search}%")
              ->orWhere('deskripsi', 'like', "%{$search}%")
              ->orWhere('syarat_prosedur', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getBiayaFormatAttribute()
    {
        if ($this->biaya) {
            return 'Rp ' . number_format($this->biaya, 0, ',', '.');
        }
        return 'Gratis';
    }
}