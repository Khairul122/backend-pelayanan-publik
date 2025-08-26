<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_saran' => $this->id_saran,
            'jenis_saran' => $this->jenis_saran,
            'subjek' => $this->subjek,
            'isi' => $this->isi,
            'nama_pengirim' => $this->nama_pengirim_display,
            'email_pengirim' => $this->when(!$this->is_anonim, $this->email_pengirim),
            'no_hp' => $this->when(!$this->is_anonim && request()->user()?->id === $this->id_pengguna, $this->no_hp),
            'status' => $this->status,
            'status_badge' => $this->status_badge,
            'prioritas' => $this->prioritas,
            'prioritas_badge' => $this->prioritas_badge,
            'is_public' => $this->is_public,
            'is_anonim' => $this->is_anonim,
            'lampiran' => $this->lampiran,
            'lampiran_urls' => $this->lampiran_urls,
            'balasan_admin' => $this->balasan_admin,
            'tanggal_balasan' => $this->tanggal_balasan?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'pengguna' => $this->when(
                !$this->is_anonim && $this->relationLoaded('pengguna'),
                [
                    'id' => $this->pengguna?->id,
                    'name' => $this->pengguna?->name,
                    'email' => $this->pengguna?->email,
                ]
            ),
            'admin_pembalas' => $this->when(
                $this->relationLoaded('adminPembalas') && $this->adminPembalas,
                [
                    'id' => $this->adminPembalas?->id,
                    'name' => $this->adminPembalas?->name,
                ]
            ),
        ];
    }
}