<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisLayanan;
use App\Models\Layanan;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        // Create Jenis Layanan
        $jenisLayanans = [
            [
                'nama_jenis' => 'Administrasi Kependudukan',
                'deskripsi' => 'Layanan yang berkaitan dengan administrasi kependudukan',
                'is_active' => true,
            ],
            [
                'nama_jenis' => 'Kesehatan',
                'deskripsi' => 'Layanan kesehatan masyarakat',
                'is_active' => true,
            ],
            [
                'nama_jenis' => 'Pendidikan',
                'deskripsi' => 'Layanan pendidikan dan pembelajaran',
                'is_active' => true,
            ],
            [
                'nama_jenis' => 'Perizinan',
                'deskripsi' => 'Layanan perizinan dan regulasi',
                'is_active' => true,
            ],
        ];

        foreach ($jenisLayanans as $jenis) {
            JenisLayanan::create($jenis);
        }

        // Create Layanan
        $layanans = [
            [
                'nama_layanan' => 'Penerbitan KTP Elektronik',
                'deskripsi' => 'Layanan penerbitan Kartu Tanda Penduduk elektronik untuk warga negara Indonesia',
                'syarat_prosedur' => 'Syarat: Fotokopi KK, Surat Pengantar RT/RW, Pas foto 3x4 2 lembar. Prosedur: Datang ke kantor kecamatan, mengisi formulir, foto dan sidik jari, tunggu proses 14 hari kerja.',
                'id_jenis_layanan' => 1,
                'durasi_pelayanan' => '14 hari kerja',
                'biaya' => 0,
                'is_online' => false,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'nama_layanan' => 'Penerbitan Kartu Keluarga',
                'deskripsi' => 'Layanan penerbitan Kartu Keluarga baru atau perubahan data keluarga',
                'syarat_prosedur' => 'Syarat: Surat Nikah, KTP suami istri, Akta kelahiran anak, Surat Pengantar RT/RW. Prosedur: Datang ke kantor kelurahan, mengisi formulir, verifikasi dokumen, tunggu proses 7 hari kerja.',
                'id_jenis_layanan' => 1,
                'durasi_pelayanan' => '7 hari kerja',
                'biaya' => 0,
                'is_online' => false,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'nama_layanan' => 'Surat Keterangan Sehat',
                'deskripsi' => 'Penerbitan surat keterangan sehat untuk berbagai keperluan',
                'syarat_prosedur' => 'Syarat: KTP, Pas foto 3x4 1 lembar. Prosedur: Datang ke puskesmas, pemeriksaan kesehatan oleh dokter, tunggu hasil 1 hari kerja.',
                'id_jenis_layanan' => 2,
                'durasi_pelayanan' => '1 hari kerja',
                'biaya' => 25000,
                'is_online' => false,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'nama_layanan' => 'Pendaftaran Sekolah Online',
                'deskripsi' => 'Pendaftaran siswa baru secara online untuk sekolah negeri',
                'syarat_prosedur' => 'Syarat: Ijazah jenjang sebelumnya, KK, Akta kelahiran, Pas foto. Prosedur: Daftar online di website resmi, upload dokumen, verifikasi, pengumuman hasil.',
                'id_jenis_layanan' => 3,
                'durasi_pelayanan' => 'Sesuai jadwal penerimaan',
                'biaya' => 0,
                'is_online' => true,
                'link_online' => 'https://ppdb.kemdikbud.go.id',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'nama_layanan' => 'Izin Usaha Mikro Kecil',
                'deskripsi' => 'Penerbitan izin usaha untuk usaha mikro dan kecil',
                'syarat_prosedur' => 'Syarat: KTP, KK, Surat keterangan domisili usaha, Pas foto. Prosedur: Datang ke kantor kecamatan, mengisi formulir, verifikasi lokasi usaha, tunggu proses 7 hari kerja.',
                'id_jenis_layanan' => 4,
                'durasi_pelayanan' => '7 hari kerja',
                'biaya' => 50000,
                'is_online' => true,
                'link_online' => 'https://oss.go.id',
                'is_active' => true,
                'created_by' => 1,
            ],
        ];

        foreach ($layanans as $layanan) {
            Layanan::create($layanan);
        }
    }
}