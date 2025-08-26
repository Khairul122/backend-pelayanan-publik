<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class JenisLayanan extends Model
{
    use HasFactory;

    protected $table = 'jenis_layanans';
    protected $primaryKey = 'id_jenis_layanan';

    protected $fillable = [
        'nama_jenis',
        'deskripsi',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function layanans()
    {
        return $this->hasMany(Layanan::class, 'id_jenis_layanan', 'id_jenis_layanan');
    }

    public function layananAktif()
    {
        return $this->hasMany(Layanan::class, 'id_jenis_layanan', 'id_jenis_layanan')
                   ->where('is_active', true);
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return url('storage/icons/' . $this->icon);
        }
        return null;
    }
}