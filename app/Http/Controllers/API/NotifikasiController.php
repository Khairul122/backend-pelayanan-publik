<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiResource;
use App\Models\Notifikasi;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Notifikasi",
 *     description="Notifikasi management endpoints"
 * )
 */
class NotifikasiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifikasi",
     *     tags={"Notifikasi"},
     *     summary="Get user notifications",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="jenis",
     *         in="query",
     *         description="Filter by jenis",
     *         required=false,
     *         @OA\Schema(type="string", enum={"info", "peringatan", "sukses", "error", "pengumuman"})
     *     ),
     *     @OA\Parameter(
     *         name="kategori",
     *         in="query",
     *         description="Filter by kategori",
     *         required=false,
     *         @OA\Schema(type="string", enum={"system", "layanan", "berita", "saran", "custom"})
     *     ),
     *     @OA\Parameter(
     *         name="unread_only",
     *         in="query",
     *         description="Show unread only",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user notifications"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = Notifikasi::forUser($user->id)
                ->active()
                ->with(['creator'])
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc');

            // Filter by jenis
            if ($request->has('jenis')) {
                $query->byJenis($request->jenis);
            }

            // Filter by kategori
            if ($request->has('kategori')) {
                $query->byKategori($request->kategori);
            }

            // Filter unread only
            if ($request->boolean('unread_only')) {
                $query->unread();
            }

            $notifikasi = $query->paginate(20);

            // Count unread notifications
            $unreadCount = Notifikasi::forUser($user->id)
                ->active()
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil diambil',
                'data' => NotifikasiResource::collection($notifikasi),
                'unread_count' => $unreadCount,
                'pagination' => [
                    'current_page' => $notifikasi->currentPage(),
                    'per_page' => $notifikasi->perPage(),
                    'total' => $notifikasi->total(),
                    'last_page' => $notifikasi->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/notifikasi/{id}",
     *     tags={"Notifikasi"},
     *     summary="Get single notification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification details"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            
            $notifikasi = Notifikasi::with(['creator'])
                ->where('id_notifikasi', $id)
                ->where(function($q) use ($user) {
                    $q->where('id_pengguna', $user->id)
                      ->orWhere('is_broadcast', true);
                })
                ->firstOrFail();

            // Auto mark as read
            if (!$notifikasi->is_read) {
                $notifikasi->markAsRead($user->id);
                $notifikasi->refresh();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil diambil',
                'data' => new NotifikasiResource($notifikasi)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/notifikasi/{id}/read",
     *     tags={"Notifikasi"},
     *     summary="Mark notification as read",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read"
     *     )
     * )
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            $notifikasi = Notifikasi::where('id_notifikasi', $id)
                ->where(function($q) use ($user) {
                    $q->where('id_pengguna', $user->id)
                      ->orWhere('is_broadcast', true);
                })
                ->firstOrFail();

            $notifikasi->markAsRead($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca',
                'data' => new NotifikasiResource($notifikasi->fresh(['creator']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/notifikasi/read-all",
     *     tags={"Notifikasi"},
     *     summary="Mark all notifications as read",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read"
     *     )
     * )
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            $updated = Notifikasi::forUser($user->id)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "Semua notifikasi ({$updated}) ditandai sudah dibaca"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/notifikasi-count",
     *     tags={"Notifikasi"},
     *     summary="Get unread notification count",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unread notification count"
     *     )
     * )
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            
            $count = Notifikasi::forUser($user->id)
                ->active()
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Jumlah notifikasi belum dibaca',
                'unread_count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jumlah notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/notifikasi/{id}",
     *     tags={"Notifikasi"},
     *     summary="Delete notification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            $notifikasi = Notifikasi::where('id_notifikasi', $id)
                ->where(function($q) use ($user) {
                    $q->where('id_pengguna', $user->id)
                      ->orWhere('is_broadcast', true);
                })
                ->firstOrFail();

            $notifikasi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== ADMIN METHODS ====================

    /**
     * @OA\Get(
     *     path="/admin/notifikasi",
     *     tags={"Notifikasi"},
     *     summary="Get all notifications for admin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all notifications"
     *     )
     * )
     */
    public function adminIndex(Request $request)
    {
        try {
            $query = Notifikasi::with(['pengguna', 'creator'])
                ->orderBy('created_at', 'desc');

            // Filters
            if ($request->has('jenis')) {
                $query->byJenis($request->jenis);
            }

            if ($request->has('kategori')) {
                $query->byKategori($request->kategori);
            }

            if ($request->has('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->boolean('broadcast_only')) {
                $query->broadcast();
            }

            $notifikasi = $query->paginate(20);

            // Statistics
            $stats = [
                'total' => Notifikasi::count(),
                'broadcast' => Notifikasi::where('is_broadcast', true)->count(),
                'by_jenis' => [
                    'info' => Notifikasi::byJenis('info')->count(),
                    'peringatan' => Notifikasi::byJenis('peringatan')->count(),
                    'sukses' => Notifikasi::byJenis('sukses')->count(),
                    'error' => Notifikasi::byJenis('error')->count(),
                    'pengumuman' => Notifikasi::byJenis('pengumuman')->count(),
                ],
                'recent' => Notifikasi::recent(7)->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data notifikasi berhasil diambil',
                'data' => NotifikasiResource::collection($notifikasi),
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $notifikasi->currentPage(),
                    'per_page' => $notifikasi->perPage(),
                    'total' => $notifikasi->total(),
                    'last_page' => $notifikasi->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/notifikasi",
     *     tags={"Notifikasi"},
     *     summary="Create new notification",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"judul","isi"},
     *             @OA\Property(property="judul", type="string"),
     *             @OA\Property(property="isi", type="string"),
     *             @OA\Property(property="jenis", type="string", enum={"info", "peringatan", "sukses", "error", "pengumuman"}),
     *             @OA\Property(property="kategori", type="string", enum={"system", "layanan", "berita", "saran", "custom"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "normal", "high", "urgent"}),
     *             @OA\Property(property="is_broadcast", type="boolean"),
     *             @OA\Property(property="id_pengguna", type="integer"),
     *             @OA\Property(property="action_url", type="string"),
     *             @OA\Property(property="action_text", type="string"),
     *             @OA\Property(property="expired_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Notification created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'judul' => 'required|string|max:255',
                'isi' => 'required|string',
                'jenis' => 'required|in:info,peringatan,sukses,error,pengumuman',
                'kategori' => 'required|in:system,layanan,berita,saran,custom',
                'priority' => 'required|in:low,normal,high,urgent',
                'is_broadcast' => 'boolean',
                'id_pengguna' => 'nullable|exists:users,id|required_if:is_broadcast,false',
                'icon' => 'nullable|string|max:100',
                'action_url' => 'nullable|url',
                'action_text' => 'nullable|string|max:50',
                'expired_at' => 'nullable|date|after:now',
                'data' => 'nullable|array',
            ], [
                'judul.required' => 'Judul notifikasi harus diisi',
                'isi.required' => 'Isi notifikasi harus diisi',
                'jenis.required' => 'Jenis notifikasi harus dipilih',
                'kategori.required' => 'Kategori notifikasi harus dipilih',
                'id_pengguna.required_if' => 'Pengguna harus dipilih jika bukan broadcast',
                'expired_at.after' => 'Tanggal kedaluwarsa harus di masa depan',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['created_by'] = Auth::id();

            // Jika broadcast, hapus id_pengguna
            if ($request->boolean('is_broadcast')) {
                $data['id_pengguna'] = null;
            }

            $notifikasi = Notifikasi::create($data);

            // Log activity
            LogActivity::create([
                'user_id' => Auth::id(),
                'aktivitas' => 'Create Notification - ' . $notifikasi->judul,
                'ip_address' => request()->ip(),
                'waktu' => now(),
                'status' => 'Success',
            ]);

            $notifikasi->load(['pengguna', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dibuat',
                'data' => new NotifikasiResource($notifikasi)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/notifikasi/broadcast",
     *     tags={"Notifikasi"},
     *     summary="Send broadcast notification",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"judul","isi"},
     *             @OA\Property(property="judul", type="string"),
     *             @OA\Property(property="isi", type="string"),
     *             @OA\Property(property="jenis", type="string", enum={"info", "peringatan", "sukses", "error", "pengumuman"}),
     *             @OA\Property(property="kategori", type="string", enum={"system", "layanan", "berita", "saran", "custom"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "normal", "high", "urgent"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Broadcast notification sent"
     *     )
     * )
     */
    public function broadcast(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'judul' => 'required|string|max:255',
                'isi' => 'required|string',
                'jenis' => 'required|in:info,peringatan,sukses,error,pengumuman',
                'kategori' => 'required|in:system,layanan,berita,saran,custom',
                'priority' => 'required|in:low,normal,high,urgent',
                'icon' => 'nullable|string|max:100',
                'action_url' => 'nullable|url',
                'action_text' => 'nullable|string|max:50',
                'expired_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['is_broadcast'] = true;
            $data['id_pengguna'] = null;

            $notifikasi = Notifikasi::create($data);

            // Log activity
            LogActivity::create([
                'user_id' => Auth::id(),
                'aktivitas' => 'Broadcast Notification - ' . $notifikasi->judul,
                'ip_address' => request()->ip(),
                'waktu' => now(),
                'status' => 'Success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi broadcast berhasil dikirim',
                'data' => new NotifikasiResource($notifikasi)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim broadcast',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/notifikasi/{id}",
     *     tags={"Notifikasi"},
     *     summary="Delete notification (admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted"
     *     )
     * )
     */
    public function adminDestroy($id)
    {
        try {
            $notifikasi = Notifikasi::findOrFail($id);
            $judul = $notifikasi->judul;
            
            $notifikasi->delete();

            // Log activity
            LogActivity::create([
                'user_id' => Auth::id(),
                'aktivitas' => 'Delete Notification - ' . $judul,
                'ip_address' => request()->ip(),
                'waktu' => now(),
                'status' => 'Success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/notifikasi/statistics",
     *     tags={"Notifikasi"},
     *     summary="Get notification statistics",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Notification statistics"
     *     )
     * )
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_notifications' => Notifikasi::count(),
                'broadcast_notifications' => Notifikasi::where('is_broadcast', true)->count(),
                'active_notifications' => Notifikasi::active()->count(),
                'expired_notifications' => Notifikasi::expired()->count(),
                'by_jenis' => [
                    'info' => Notifikasi::byJenis('info')->count(),
                    'peringatan' => Notifikasi::byJenis('peringatan')->count(),
                    'sukses' => Notifikasi::byJenis('sukses')->count(),
                    'error' => Notifikasi::byJenis('error')->count(),
                    'pengumuman' => Notifikasi::byJenis('pengumuman')->count(),
                ],
                'by_kategori' => [
                    'system' => Notifikasi::byKategori('system')->count(),
                    'layanan' => Notifikasi::byKategori('layanan')->count(),
                    'berita' => Notifikasi::byKategori('berita')->count(),
                    'saran' => Notifikasi::byKategori('saran')->count(),
                    'custom' => Notifikasi::byKategori('custom')->count(),
                ],
                'by_priority' => [
                    'low' => Notifikasi::byPriority('low')->count(),
                    'normal' => Notifikasi::byPriority('normal')->count(),
                    'high' => Notifikasi::byPriority('high')->count(),
                    'urgent' => Notifikasi::byPriority('urgent')->count(),
                ],
                'recent_activity' => Notifikasi::with(['creator'])
                    ->recent(7)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(['id_notifikasi', 'judul', 'jenis', 'is_broadcast', 'created_at', 'created_by']),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik notifikasi berhasil diambil',
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