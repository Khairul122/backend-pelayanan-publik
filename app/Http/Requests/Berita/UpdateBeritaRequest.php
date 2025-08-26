<?php

namespace App\Http\Requests\Berita;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBeritaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => 'sometimes|required|string|max:255',
            'isi' => 'sometimes|required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_kategori' => 'sometimes|required|exists:kategori_beritas,id_kategori',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul berita harus diisi',
            'judul.max' => 'Judul berita maksimal 255 karakter',
            'isi.required' => 'Isi berita harus diisi',
            'gambar.image' => 'File harus berupa gambar',
            'gambar.max' => 'Ukuran gambar maksimal 2MB',
            'id_kategori.required' => 'Kategori berita harus dipilih',
            'id_kategori.exists' => 'Kategori berita tidak valid',
        ];
    }
}