<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotifikasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_notifikasi' => $this->id_notifikasi,
            'judul' => $this->judul,
            'isi' => $this->isi,
            'jenis' => $this->jenis,
            'jenis_badge' => $this->jenis_badge,
            'kategori' => $this->kategori,
            'icon' => $this->icon,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
            'has_action' => $this->has_action,
            'data' => $this->data,
            'is_read' => $this->is_read,
            'is_broadcast' => $this->is_broadcast,
            'priority' => $this->priority,
            'priority_badge' => $this->priority_badge,
            'is_expired' => $this->is_expired,
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'expired_at' => $this->expired_at?->format('Y-m-d H:i:s'),
            'time_ago' => $this->time_ago,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'pengguna' => $this->when(
                !$this->is_broadcast && $this->relationLoaded('pengguna'),
                [
                    'id' => $this->pengguna?->id,
                    'name' => $this->pengguna?->name,
                    'email' => $this->pengguna?->email,
                ]
            ),
            'creator' => $this->when(
                $this->relationLoaded('creator'),
                [
                    'id' => $this->creator?->id,
                    'name' => $this->creator?->name,
                ]
            ),
        ];
    }
}