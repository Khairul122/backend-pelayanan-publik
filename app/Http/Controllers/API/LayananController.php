<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Layanan\StoreLayananRequest;
use App\Http\Requests\Layanan\UpdateLayananRequest;
use App\Http\Resources\LayananResource;
use App\Models\Layanan;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Layanan",
 *     description="Layanan Publik management endpoints"
 * )
 */
class LayananController extends Controller
{
    /**
     * @OA\Get(
     *     path="/layanan",
     *     tags={"Layanan"},
     *     summary="Get all layanan publik",
     *     @OA\Parameter(
     *         name="jenis",
     *         in="query",
     *         description="Filter by jenis layanan ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="online",
     *         in="query",
     *         description="Filter online layanan only",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search layanan",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of layanan"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Layanan::with(['jenisLayanan', 'author'])
                ->active()
                ->orderBy('nama_layanan');

            // Filter by jenis layanan
            if ($request->has('jenis')) {
                $query->byJenis($request->jenis);
            }

            // Filter online layanan only
            if ($request->boolean('online')) {
                $query->online();
            }

            // Search functionality
            if ($request->has('search')) {
                $query->search($request->search);
            }

            $layanans = $query->paginate(12);

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil diambil',
                'data' => LayananResource::collection($layanans),
                'pagination' => [
                    'current_page' => $layanans->currentPage(),
                    'per_page' => $layanans->perPage(),
                    'total' => $layanans->total(),
                    'last_page' => $layanans->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/layanan/{id}",
     *     tags={"Layanan"},
     *     summary="Get single layanan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Layanan details"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $layanan = Layanan::with(['jenisLayanan', 'author'])
                ->where('id_layanan', $id)
                ->active()
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil diambil',
                'data' => new LayananResource($layanan)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/layanan",
     *     tags={"Layanan"},
     *     summary="Create new layanan",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_layanan","deskripsi","syarat_prosedur","id_jenis_layanan"},
     *             @OA\Property(property="nama_layanan", type="string"),
     *             @OA\Property(property="deskripsi", type="string"),
     *             @OA\Property(property="syarat_prosedur", type="string"),
     *             @OA\Property(property="id_jenis_layanan", type="integer"),
     *             @OA\Property(property="durasi_pelayanan", type="string"),
     *             @OA\Property(property="biaya", type="number"),
     *             @OA\Property(property="is_online", type="boolean"),
     *             @OA\Property(property="link_online", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Layanan created successfully"
     *     )
     * )
     */
    public function store(StoreLayananRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = Auth::id();

            $layanan = Layanan::create($data);
            $layanan->load(['jenisLayanan', 'author']);

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil dibuat',
                'data' => new LayananResource($layanan)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/layanan/{id}",
     *     tags={"Layanan"},
     *     summary="Update layanan",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Layanan updated successfully"
     *     )
     * )
     */
    public function update(UpdateLayananRequest $request, $id)
    {
        try {
            $layanan = Layanan::findOrFail($id);
            $data = $request->validated();

            $layanan->update($data);
            $layanan->load(['jenisLayanan', 'author']);

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil diupdate',
                'data' => new LayananResource($layanan)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/layanan/{id}",
     *     tags={"Layanan"},
     *     summary="Delete layanan",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Layanan deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $layanan = Layanan::findOrFail($id);
            $layanan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}