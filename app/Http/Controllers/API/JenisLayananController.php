<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\JenisLayananResource;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Jenis Layanan",
 *     description="Jenis Layanan management endpoints"
 * )
 */
class JenisLayananController extends Controller
{
    /**
     * @OA\Get(
     *     path="/jenis-layanan",
     *     tags={"Jenis Layanan"},
     *     summary="Get all jenis layanan",
     *     @OA\Response(
     *         response=200,
     *         description="List of jenis layanan"
     *     )
     * )
     */
    public function index()
    {
        try {
            $jenisLayanan = JenisLayanan::withCount(['layananAktif as layanans_count'])
                ->active()
                ->orderBy('nama_jenis')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Jenis layanan berhasil diambil',
                'data' => JenisLayananResource::collection($jenisLayanan)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jenis layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/jenis-layanan/{id}",
     *     tags={"Jenis Layanan"},
     *     summary="Get single jenis layanan with layanans",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jenis layanan with layanans"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $jenisLayanan = JenisLayanan::with(['layananAktif' => function($query) {
                $query->orderBy('nama_layanan')->limit(10);
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Jenis layanan berhasil diambil',
                'data' => [
                    'jenis_layanan' => new JenisLayananResource($jenisLayanan),
                    'layanans' => \App\Http\Resources\LayananResource::collection($jenisLayanan->layananAktif)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/jenis-layanan",
     *     tags={"Jenis Layanan"},
     *     summary="Create new jenis layanan",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nama_jenis", type="string"),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="icon", type="file"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Jenis layanan created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_jenis' => 'required|string|max:255|unique:jenis_layanans,nama_jenis',
                'deskripsi' => 'nullable|string|max:500',
                'icon' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:1024',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/icons', $filename);
                $data['icon'] = $filename;
            }

            $jenisLayanan = JenisLayanan::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Jenis layanan berhasil dibuat',
                'data' => new JenisLayananResource($jenisLayanan)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat jenis layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/jenis-layanan/{id}",
     *     tags={"Jenis Layanan"},
     *     summary="Update jenis layanan",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jenis layanan updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $jenisLayanan = JenisLayanan::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_jenis' => 'sometimes|required|string|max:255|unique:jenis_layanans,nama_jenis,' . $id . ',id_jenis_layanan',
                'deskripsi' => 'nullable|string|max:500',
                'icon' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:1024',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Handle icon upload
            if ($request->hasFile('icon')) {
                // Delete old icon
                if ($jenisLayanan->icon) {
                    Storage::delete('public/icons/' . $jenisLayanan->icon);
                }

                $file = $request->file('icon');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/icons', $filename);
                $data['icon'] = $filename;
            }

            $jenisLayanan->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Jenis layanan berhasil diupdate',
                'data' => new JenisLayananResource($jenisLayanan)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate jenis layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/jenis-layanan/{id}",
     *     tags={"Jenis Layanan"},
     *     summary="Delete jenis layanan",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jenis layanan deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $jenisLayanan = JenisLayanan::findOrFail($id);

            // Check if jenis layanan has active layanans
            if ($jenisLayanan->layananAktif()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis layanan tidak dapat dihapus karena masih memiliki layanan aktif'
                ], 400);
            }

            // Delete icon file
            if ($jenisLayanan->icon) {
                Storage::delete('public/icons/' . $jenisLayanan->icon);
            }

            $jenisLayanan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jenis layanan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jenis layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}