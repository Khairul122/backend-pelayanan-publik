<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeritaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_berita' => $this->id_berita,
            'judul' => $this->judul,
            'isi' => $this->isi,
            'gambar' => $this->gambar,
            'gambar_url' => $this->gambar_url,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'kategori' => [
                'id_kategori' => $this->kategori?->id_kategori,
                'nama_kategori' => $this->kategori?->nama_kategori,
            ],
            'author' => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
            ],
        ];
    }
}