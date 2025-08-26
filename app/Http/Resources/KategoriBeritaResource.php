<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriBeritaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_kategori' => $this->id_kategori,
            'nama_kategori' => $this->nama_kategori,
            'deskripsi' => $this->deskripsi,
            'jumlah_berita' => $this->when(
                $this->relationLoaded('beritas') || isset($this->beritas_count),
                $this->beritas_count ?? $this->beritas->count()
            ),
            'berita_terbaru' => BeritaResource::collection(
                $this->whenLoaded('beritas')
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}