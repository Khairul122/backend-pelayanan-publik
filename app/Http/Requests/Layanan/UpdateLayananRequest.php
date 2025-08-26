<?php

namespace App\Http\Requests\Layanan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_layanan' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
            'syarat_prosedur' => 'sometimes|required|string',
            'id_jenis_layanan' => 'sometimes|required|exists:jenis_layanans,id_jenis_layanan',
            'durasi_pelayanan' => 'nullable|string|max:100',
            'biaya' => 'nullable|numeric|min:0',
            'catatan_khusus' => 'nullable|string',
            'is_online' => 'boolean',
            'link_online' => 'nullable|url|required_if:is_online,true',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_layanan.required' => 'Nama layanan harus diisi',
            'nama_layanan.max' => 'Nama layanan maksimal 255 karakter',
            'deskripsi.required' => 'Deskripsi layanan harus diisi',
            'syarat_prosedur.required' => 'Syarat dan prosedur harus diisi',
            'id_jenis_layanan.required' => 'Jenis layanan harus dipilih',
            'id_jenis_layanan.exists' => 'Jenis layanan tidak valid',
            'biaya.numeric' => 'Biaya harus berupa angka',
            'biaya.min' => 'Biaya tidak boleh negatif',
            'link_online.url' => 'Link online harus berupa URL yang valid',
            'link_online.required_if' => 'Link online harus diisi jika layanan online',
        ];
    }
}