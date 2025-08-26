<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JenisLayananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_jenis_layanan' => $this->id_jenis_layanan,
            'nama_jenis' => $this->nama_jenis,
            'deskripsi' => $this->deskripsi,
            'icon' => $this->icon,
            'icon_url' => $this->icon_url,
            'is_active' => $this->is_active,
            'jumlah_layanan' => $this->when(
                $this->relationLoaded('layanans') || isset($this->layanans_count),
                $this->layanans_count ?? $this->layanans->count()
            ),
            'layanan_online' => $this->when(
                $this->relationLoaded('layanans'),
                $this->layanans->where('is_online', true)->count()
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}