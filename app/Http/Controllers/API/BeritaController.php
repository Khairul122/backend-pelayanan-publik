<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Berita\StoreBeritaRequest;
use App\Http\Requests\Berita\UpdateBeritaRequest;
use App\Http\Resources\BeritaResource;
use App\Models\Berita;
use App\Models\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Berita",
 *     description="Berita management endpoints"
 * )
 */
class BeritaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/berita",
     *     tags={"Berita"},
     *     summary="Get all berita",
     *     @OA\Parameter(
     *         name="kategori",
     *         in="query",
     *         description="Filter by kategori ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by title or content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of berita"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Berita::with(['kategori', 'author'])
                ->published()
                ->latest();

            // Filter by kategori
            if ($request->has('kategori')) {
                $query->byKategori($request->kategori);
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('isi', 'like', "%{$search}%");
                });
            }

            $beritas = $query->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil diambil',
                'data' => BeritaResource::collection($beritas),
                'pagination' => [
                    'current_page' => $beritas->currentPage(),
                    'per_page' => $beritas->perPage(),
                    'total' => $beritas->total(),
                    'last_page' => $beritas->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/berita/{id}",
     *     tags={"Berita"},
     *     summary="Get single berita",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berita details"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $berita = Berita::with(['kategori', 'author'])
                ->where('id_berita', $id)
                ->published()
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil diambil',
                'data' => new BeritaResource($berita)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/berita",
     *     tags={"Berita"},
     *     summary="Create new berita",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="judul", type="string"),
     *                 @OA\Property(property="isi", type="string"),
     *                 @OA\Property(property="gambar", type="file"),
     *                 @OA\Property(property="id_kategori", type="integer"),
     *                 @OA\Property(property="is_published", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Berita created successfully"
     *     )
     * )
     */
    public function store(StoreBeritaRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = Auth::id();
            $data['published_at'] = $data['is_published'] ?? true ? now() : null;

            // Handle file upload
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/berita', $filename);
                $data['gambar'] = $filename;
            }

            $berita = Berita::create($data);
            $berita->load(['kategori', 'author']);

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil dibuat',
                'data' => new BeritaResource($berita)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/berita/{id}",
     *     tags={"Berita"},
     *     summary="Update berita",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berita updated successfully"
     *     )
     * )
     */
    public function update(UpdateBeritaRequest $request, $id)
    {
        try {
            $berita = Berita::findOrFail($id);
            $data = $request->validated();

            // Handle file upload
            if ($request->hasFile('gambar')) {
                // Delete old image
                if ($berita->gambar) {
                    Storage::delete('public/berita/' . $berita->gambar);
                }

                $file = $request->file('gambar');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/berita', $filename);
                $data['gambar'] = $filename;
            }

            // Update published_at if status changed
            if (isset($data['is_published'])) {
                $data['published_at'] = $data['is_published'] ? now() : null;
            }

            $berita->update($data);
            $berita->load(['kategori', 'author']);

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil diupdate',
                'data' => new BeritaResource($berita)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/berita/{id}",
     *     tags={"Berita"},
     *     summary="Delete berita",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berita deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $berita = Berita::findOrFail($id);

            // Delete image file
            if ($berita->gambar) {
                Storage::delete('public/berita/' . $berita->gambar);
            }

            $berita->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus berita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/home",
     *     tags={"Berita"},
     *     summary="Get home data with latest berita",
     *     @OA\Response(
     *         response=200,
     *         description="Home data with latest berita"
     *     )
     * )
     */
    public function homeData()
    {
        try {
            // Latest berita for home
            $latestBerita = Berita::with(['kategori'])
                ->published()
                ->latest()
                ->limit(5)
                ->get();

            // Categories with berita count
            $categories = KategoriBerita::withCount(['beritas' => function($query) {
                $query->published();
            }])->get();

            return response()->json([
                'success' => true,
                'message' => 'Data home berhasil diambil',
                'data' => [
                    'latest_berita' => BeritaResource::collection($latestBerita),
                    'categories' => $categories,
                    'stats' => [
                        'total_berita' => Berita::published()->count(),
                        'total_categories' => KategoriBerita::count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data home',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}