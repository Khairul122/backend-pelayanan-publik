<?php

namespace App\Http\Requests\Saran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'jenis_saran' => 'required|in:saran,keluhan,kritik,pertanyaan',
            'subjek' => 'required|string|max:255',
            'isi' => 'required|string|min:10',
            'is_anonim' => 'boolean',
            'lampiran' => 'nullable|array|max:3',
            'lampiran.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ];

        // Jika user tidak login, wajib isi data kontak
        if (!Auth::check()) {
            $rules['nama_pengirim'] = 'required|string|max:100';
            $rules['email_pengirim'] = 'required|email|max:100';
            $rules['no_hp'] = 'nullable|string|max:15';
        } else {
            $rules['nama_pengirim'] = 'nullable|string|max:100';
            $rules['email_pengirim'] = 'nullable|email|max:100';
            $rules['no_hp'] = 'nullable|string|max:15';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'jenis_saran.required' => 'Jenis saran harus dipilih',
            'jenis_saran.in' => 'Jenis saran tidak valid',
            'subjek.required' => 'Subjek harus diisi',
            'subjek.max' => 'Subjek maksimal 255 karakter',
            'isi.required' => 'Isi saran harus diisi',
            'isi.min' => 'Isi saran minimal 10 karakter',
            'nama_pengirim.required' => 'Nama pengirim harus diisi',
            'email_pengirim.required' => 'Email pengirim harus diisi',
            'email_pengirim.email' => 'Format email tidak valid',
            'no_hp.max' => 'Nomor HP maksimal 15 karakter',
            'lampiran.max' => 'Maksimal 3 file lampiran',
            'lampiran.*.file' => 'Lampiran harus berupa file',
            'lampiran.*.mimes' => 'Format file tidak didukung (gunakan pdf, doc, docx, jpg, jpeg, png)',
            'lampiran.*.max' => 'Ukuran file maksimal 2MB',
        ];
    }
}