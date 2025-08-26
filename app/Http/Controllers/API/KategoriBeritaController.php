<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriBeritaResource;
use App\Models\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Kategori Berita",
 *     description="Kategori Berita management endpoints"
 * )
 */
class KategoriBeritaController extends Controller
{
    /**
     /**
     * @OA\Get(
     *     path="/kategori-berita",
     *     tags={"Kategori Berita"},
     *     summary="Get all kategori berita",
     *     @OA\Response(
     *         response=200,
     *         description="List of kategori berita",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori berita berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_kategori", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Pengumuman"),
     *                     @OA\Property(property="deskripsi", type="string", example="Kategori untuk pengumuman resmi"),
     *                     @OA\Property(property="beritas_count", type="integer", example=5),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-23T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-23T12:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        try {
            $kategories = KategoriBerita::withCount('beritas')->get();

            return response()->json([
                'success' => true,
                'message' => 'Kategori berita berhasil diambil',
                'data' => KategoriBeritaResource::collection($kategories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil kategori berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/kategori-berita",
     *     tags={"Kategori Berita"},
     *     summary="Create new kategori berita",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_kategori"},
     *             @OA\Property(property="nama_kategori", type="string", example="Pengumuman"),
     *             @OA\Property(property="deskripsi", type="string", example="Kategori untuk pengumuman resmi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kategori berita created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'required|string|max:255|unique:kategori_beritas,nama_kategori',
                'deskripsi' => 'nullable|string|max:500',
            ], [
                'nama_kategori.required' => 'Nama kategori harus diisi',
                'nama_kategori.unique' => 'Nama kategori sudah digunakan',
                'nama_kategori.max' => 'Nama kategori maksimal 255 karakter',
                'deskripsi.max' => 'Deskripsi maksimal 500 karakter',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kategori = KategoriBerita::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kategori berita berhasil dibuat',
                'data' => new KategoriBeritaResource($kategori)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kategori berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/kategori-berita/{id}",
     *     tags={"Kategori Berita"},
     *     summary="Get single kategori berita",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori berita details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori berita not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $kategori = KategoriBerita::with(['beritas' => function ($query) {
                $query->published()->latest()->limit(10);
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berita berhasil diambil',
                'data' => new KategoriBeritaResource($kategori)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori berita tidak ditemukan'
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/kategori-berita/{id}",
     *     tags={"Kategori Berita"},
     *     summary="Update kategori berita",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama_kategori", type="string", example="Pengumuman Updated"),
     *             @OA\Property(property="deskripsi", type="string", example="Deskripsi updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori berita updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori berita not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $kategori = KategoriBerita::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'sometimes|required|string|max:255|unique:kategori_beritas,nama_kategori,' . $id . ',id_kategori',
                'deskripsi' => 'nullable|string|max:500',
            ], [
                'nama_kategori.required' => 'Nama kategori harus diisi',
                'nama_kategori.unique' => 'Nama kategori sudah digunakan',
                'nama_kategori.max' => 'Nama kategori maksimal 255 karakter',
                'deskripsi.max' => 'Deskripsi maksimal 500 karakter',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kategori->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kategori berita berhasil diupdate',
                'data' => new KategoriBeritaResource($kategori)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate kategori berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/kategori-berita/{id}",
     *     tags={"Kategori Berita"},
     *     summary="Delete kategori berita",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori berita deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori berita not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete kategori with existing berita"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $kategori = KategoriBerita::findOrFail($id);

            // Check if kategori has beritas
            if ($kategori->beritas()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki berita'
                ], 400);
            }

            $kategori->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori berita berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/kategori-berita/{id}/berita",
     *     tags={"Kategori Berita"},
     *     summary="Get berita by kategori",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of berita to return",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berita by kategori"
     *     )
     * )
     */
    public function getBerita(string $id, Request $request)
    {
        try {
            $kategori = KategoriBerita::findOrFail($id);
            $limit = $request->get('limit', 10);

            $beritas = $kategori->beritas()
                ->with('author')
                ->published()
                ->latest()
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil diambil',
                'data' => [
                    'kategori' => new KategoriBeritaResource($kategori),
                    'beritas' => \App\Http\Resources\BeritaResource::collection($beritas),
                    'pagination' => [
                        'current_page' => $beritas->currentPage(),
                        'per_page' => $beritas->perPage(),
                        'total' => $beritas->total(),
                        'last_page' => $beritas->lastPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori berita tidak ditemukan'
            ], 404);
        }
    }
}
