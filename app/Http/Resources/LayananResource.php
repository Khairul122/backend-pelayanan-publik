<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LayananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_layanan' => $this->id_layanan,
            'nama_layanan' => $this->nama_layanan,
            'deskripsi' => $this->deskripsi,
            'syarat_prosedur' => $this->syarat_prosedur,
            'durasi_pelayanan' => $this->durasi_pelayanan,
            'biaya' => $this->biaya,
            'biaya_format' => $this->biaya_format,
            'catatan_khusus' => $this->catatan_khusus,
            'is_online' => $this->is_online,
            'link_online' => $this->link_online,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'jenis_layanan' => [
                'id_jenis_layanan' => $this->jenisLayanan?->id_jenis_layanan,
                'nama_jenis' => $this->jenisLayanan?->nama_jenis,
                'icon' => $this->jenisLayanan?->icon,
                'icon_url' => $this->jenisLayanan?->icon_url,
            ],
            'author' => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
            ],
        ];
    }
}