<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Saran\StoreSaranRequest;
use App\Http\Resources\SaranResource;
use App\Models\Saran;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Saran & Bantuan",
 *     description="Saran dan Bantuan management endpoints"
 * )
 */
class SaranController extends Controller
{
    /**
     * @OA\Get(
     *     path="/saran",
     *     tags={"Saran & Bantuan"},
     *     summary="Get public saran list",
     *     @OA\Parameter(
     *         name="jenis",
     *         in="query",
     *         description="Filter by jenis saran",
     *         required=false,
     *         @OA\Schema(type="string", enum={"saran", "keluhan", "kritik", "pertanyaan"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search saran",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of public saran"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Saran::with(['pengguna', 'adminPembalas'])
                ->public()
                ->orderBy('created_at', 'desc');

            // Filter by jenis saran
            if ($request->has('jenis')) {
                $query->byJenis($request->jenis);
            }

            // Search functionality
            if ($request->has('search')) {
                $query->search($request->search);
            }

            $sarans = $query->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Saran publik berhasil diambil',
                'data' => SaranResource::collection($sarans),
                'pagination' => [
                    'current_page' => $sarans->currentPage(),
                    'per_page' => $sarans->perPage(),
                    'total' => $sarans->total(),
                    'last_page' => $sarans->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil saran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/saran",
     *     tags={"Saran & Bantuan"},
     *     summary="Submit new saran",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"jenis_saran","subjek","isi"},
     *                 @OA\Property(property="jenis_saran", type="string", enum={"saran", "keluhan", "kritik", "pertanyaan"}),
     *                 @OA\Property(property="subjek", type="string"),
     *                 @OA\Property(property="isi", type="string"),
     *                 @OA\Property(property="nama_pengirim", type="string"),
     *                 @OA\Property(property="email_pengirim", type="string"),
     *                 @OA\Property(property="no_hp", type="string"),
     *                 @OA\Property(property="is_anonim", type="boolean"),
     *                 @OA\Property(property="lampiran", type="array", @OA\Items(type="file"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Saran submitted successfully"
     *     )
     * )
     */
    public function store(StoreSaranRequest $request)
    {
        try {
            $data = $request->validated();

            // Set user ID jika login
            if (Auth::check()) {
                $data['id_pengguna'] = Auth::id();
                // Jika tidak ada nama_pengirim, gunakan nama user
                if (empty($data['nama_pengirim'])) {
                    $data['nama_pengirim'] = Auth::user()->name;
                }
                if (empty($data['email_pengirim'])) {
                    $data['email_pengirim'] = Auth::user()->email;
                }
            } else {
                // Guest user, buat user dummy atau gunakan ID khusus
                $data['id_pengguna'] = 1; // atau buat logic khusus untuk guest
            }

            // Handle file uploads
            $lampiranFiles = [];
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/saran', $filename);
                    $lampiranFiles[] = $filename;
                }
                $data['lampiran'] = $lampiranFiles;
            }

            $saran = Saran::create($data);

            // Log activity
            LogActivity::create([
                'user_id' => Auth::id(),
                'aktivitas' => 'Submit Saran - ' . $saran->subjek,
                'ip_address' => request()->ip(),
                'waktu' => now(),
                'status' => 'Success',
            ]);

            $saran->load(['pengguna', 'adminPembalas']);

            return response()->json([
                'success' => true,
                'message' => 'Saran berhasil dikirim. Terima kasih atas masukan Anda.',
                'data' => new SaranResource($saran)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim saran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/saran/{id}",
     *     tags={"Saran & Bantuan"},
     *     summary="Get single saran",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Saran details"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $query = Saran::with(['pengguna', 'adminPembalas']);

            // Jika user login, bisa lihat saran miliknya
            if (Auth::check()) {
                $query->where(function ($q) use ($id) {
                    $q->where('id_saran', $id)
                        ->where(function ($subQ) {
                            $subQ->where('is_public', true)
                                ->orWhere('id_pengguna', Auth::id());
                        });
                });
            } else {
                // Guest hanya bisa lihat public
                $query->where('id_saran', $id)->public();
            }

            $saran = $query->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Saran berhasil diambil',
                'data' => new SaranResource($saran)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Saran tidak ditemukan'
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/my-saran",
     *     tags={"Saran & Bantuan"},
     *     summary="Get user's own saran",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's saran list"
     *     )
     * )
     */
    public function mySaran()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login untuk melihat saran Anda'
                ], 401);
            }

            $sarans = Saran::with(['adminPembalas'])
                ->where('id_pengguna', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Saran Anda berhasil diambil',
                'data' => SaranResource::collection($sarans),
                'pagination' => [
                    'current_page' => $sarans->currentPage(),
                    'per_page' => $sarans->perPage(),
                    'total' => $sarans->total(),
                    'last_page' => $sarans->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil saran Anda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/saran",
     *     tags={"Saran & Bantuan"},
     *     summary="Get all saran for admin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"menunggu", "diproses", "selesai", "ditolak"})
     *     ),
     *     @OA\Parameter(
     *         name="prioritas",
     *         in="query",
     *         description="Filter by prioritas",
     *         required=false,
     *         @OA\Schema(type="string", enum={"rendah", "normal", "tinggi", "urgent"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of all saran for admin"
     *     )
     * )
     */
    public function adminIndex(Request $request)
    {
        try {
            $query = Saran::with(['pengguna', 'adminPembalas'])
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            // Filter by prioritas
            if ($request->has('prioritas')) {
                $query->byPrioritas($request->prioritas);
            }

            // Filter by jenis
            if ($request->has('jenis')) {
                $query->byJenis($request->jenis);
            }

            // Search
            if ($request->has('search')) {
                $query->search($request->search);
            }

            $sarans = $query->paginate(15);

            // Statistics
            $stats = [
                'total' => Saran::count(),
                'menunggu' => Saran::menunggu()->count(),
                'diproses' => Saran::byStatus('diproses')->count(),
                'selesai' => Saran::byStatus('selesai')->count(),
                'urgent' => Saran::byPrioritas('urgent')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data saran berhasil diambil',
                'data' => SaranResource::collection($sarans),
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $sarans->currentPage(),
                    'per_page' => $sarans->perPage(),
                    'total' => $sarans->total(),
                    'last_page' => $sarans->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data saran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/saran/{id}/balas",
     *     tags={"Saran & Bantuan"},
     *     summary="Reply to saran",
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
     *             required={"balasan_admin","status"},
     *             @OA\Property(property="balasan_admin", type="string"),
     *             @OA\Property(property="status", type="string", enum={"diproses", "selesai", "ditolak"}),
     *             @OA\Property(property="prioritas", type="string", enum={"rendah", "normal", "tinggi", "urgent"}),
     *             @OA\Property(property="is_public", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reply sent successfully"
     *     )
     * )
     */
    public function balas(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'balasan_admin' => 'required|string',
                'status' => 'required|in:diproses,selesai,ditolak',
                'prioritas' => 'sometimes|in:rendah,normal,tinggi,urgent',
                'is_public' => 'boolean',
            ], [
                'balasan_admin.required' => 'Balasan admin harus diisi',
                'status.required' => 'Status harus dipilih',
                'status.in' => 'Status tidak valid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $saran = Saran::findOrFail($id);

            $saran->update([
                'balasan_admin' => $request->balasan_admin,
                'status' => $request->status,
                'tanggal_balasan' => now(),
                'dibalas_oleh' => Auth::id(),
                'prioritas' => $request->prioritas ?? $saran->prioritas,
                'is_public' => $request->boolean('is_public', $saran->is_public),
            ]);

            // Log activity
            LogActivity::create([
                'user_id' => Auth::id(),
                'aktivitas' => 'Reply Saran - ' . $saran->subjek,
                'ip_address' => request()->ip(),
                'waktu' => now(),
                'status' => 'Success',
            ]);

            $saran->load(['pengguna', 'adminPembalas']);

            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dikirim',
                'data' => new SaranResource($saran)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim balasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/saran/{id}",
     *     tags={"Saran & Bantuan"},
     *     summary="Delete saran",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Saran deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $saran = Saran::findOrFail($id);

            // Delete lampiran files
            if ($saran->lampiran) {
                foreach ($saran->lampiran as $file) {
                    Storage::delete('public/saran/' . $file);
                }
            }

            $saran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Saran berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus saran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/admin/saran/statistics",
     *     tags={"Saran & Bantuan"},
     *     summary="Get saran statistics",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Saran statistics"
     *     )
     * )
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_saran' => Saran::count(),
                'by_status' => [
                    'menunggu' => Saran::byStatus('menunggu')->count(),
                    'diproses' => Saran::byStatus('diproses')->count(),
                    'selesai' => Saran::byStatus('selesai')->count(),
                    'ditolak' => Saran::byStatus('ditolak')->count(),
                ],
                'by_jenis' => [
                    'saran' => Saran::byJenis('saran')->count(),
                    'keluhan' => Saran::byJenis('keluhan')->count(),
                    'kritik' => Saran::byJenis('kritik')->count(),
                    'pertanyaan' => Saran::byJenis('pertanyaan')->count(),
                ],
                'by_prioritas' => [
                    'rendah' => Saran::byPrioritas('rendah')->count(),
                    'normal' => Saran::byPrioritas('normal')->count(),
                    'tinggi' => Saran::byPrioritas('tinggi')->count(),
                    'urgent' => Saran::byPrioritas('urgent')->count(),
                ],
                'recent_activity' => Saran::with(['pengguna'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id_saran', 'subjek', 'jenis_saran', 'status', 'created_at', 'id_pengguna']),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik saran berhasil diambil',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
