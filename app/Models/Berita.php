<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Berita extends Model
{
    use HasFactory;

    protected $table = 'beritas';
    protected $primaryKey = 'id_berita';

    protected $fillable = [
        'judul',
        'isi',
        'gambar',
        'id_kategori',
        'created_by',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function kategori()
    {
        return $this->belongsTo(KategoriBerita::class, 'id_kategori', 'id_kategori');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublished(Builder $query)
    {
        return $query->where('is_published', true);
    }

    public function scopeLatest(Builder $query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeByKategori(Builder $query, $kategoriId)
    {
        return $query->where('id_kategori', $kategoriId);
    }

    // Accessors
    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            return url('storage/berita/' . $this->gambar);
        }
        return null;
    }
}