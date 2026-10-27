<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BantuanLupaNim;
use App\Models\Fakultas;
use App\Models\Prodi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminBantuanNimController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = BantuanLupaNim::query();

        if ($user->role === 'Kaprodi') {
            $query->where('prodi_id', $user->prodi_id ?: '__UNASSIGNED__');
        } elseif ($user->role === 'Fakultas') {
            $query->where('fakultas_id', $user->fakultas_id ?: -1);
        }

        $totalPermohonan = (clone $query)->count();
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $diprosesCount = (clone $query)->where('status', 'diproses')->count();
        $selesaiCount = (clone $query)->where('status', 'selesai')->count();

        $fakultas = Fakultas::orderBy('nama_fakultas')->get();
        $prodi = Prodi::orderBy('nama_prodi')->get();

        return view('admin.bantuan_nim.index', compact(
            'totalPermohonan',
            'pendingCount',
            'diprosesCount',
            'selesaiCount',
            'fakultas',
            'prodi'
        ));
    }

    public function getData(Request $request)
    {
        $user = auth()->user();
        $query = BantuanLupaNim::with(['fakultas', 'prodi'])->orderByDesc('created_at');

        if ($user->role === 'Kaprodi') {
            $query->where('prodi_id', $user->prodi_id ?: '__UNASSIGNED__');
        } elseif ($user->role === 'Fakultas') {
            $query->where('fakultas_id', $user->fakultas_id ?: -1);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('fakultas_id') && $request->fakultas_id !== 'all') {
            $query->where('fakultas_id', $request->fakultas_id);
        }

        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $query->where('prodi_id', $request->prodi_id);
        }

        $stats = [
            'total'    => number_format(BantuanLupaNim::count()),
            'pending'  => number_format(BantuanLupaNim::where('status', 'pending')->count()),
            'diproses' => number_format(BantuanLupaNim::where('status', 'diproses')->count()),
            'selesai'  => number_format(BantuanLupaNim::where('status', 'selesai')->count()),
        ];

        return DataTables::of($query)
            ->with('stats', $stats)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($row) {
                return $row->created_at ? Carbon::parse($row->created_at)->locale('id')->translatedFormat('d F Y, H:i') . ' WIB' : '-';
            })
            ->addColumn('prodi_info', function ($row) {
                $prodiNama = $row->prodi ? e($row->prodi->nama_prodi) : '-';
                $fakultasNama = $row->fakultas ? e($row->fakultas->nama_fakultas) : '-';
                return '<div><div class="fw-bold text-gray-800">' . $prodiNama . '</div><span class="fs-8 text-muted">' . $fakultasNama . '</span></div>';
            })
            ->addColumn('kontak_wa', function ($row) {
                $waUrl = $row->whatsapp_url;
                $phone = e($row->no_wa);
                return '<a href="' . e($waUrl) . '" target="_blank" class="btn btn-sm btn-light-success d-inline-flex align-items-center gap-2 py-1 px-3 rounded-pill shadow-xs" title="Hubungi via WhatsApp">
                            <i class="fab fa-whatsapp fs-4 text-success"></i>
                            <span class="fw-bold fs-7 text-gray-800">' . $phone . '</span>
                        </a>';
            })
            ->addColumn('status_badge', function ($row) {
                $status = strtolower($row->status ?? 'pending');
                if ($status === 'selesai') {
                    return '<span class="badge badge-light-success text-success fw-bold fs-7 px-3 py-2 d-inline-flex align-items-center gap-1">
                                <i class="fas fa-check-circle text-success fs-8"></i> Selesai
                            </span>';
                } elseif ($status === 'diproses') {
                    return '<span class="badge badge-light-info text-info fw-bold fs-7 px-3 py-2 d-inline-flex align-items-center gap-1">
                                <i class="fas fa-spinner fa-spin text-info fs-8"></i> Diproses
                            </span>';
                }
                return '<span class="badge badge-light-warning text-warning fw-bold fs-7 px-3 py-2 d-inline-flex align-items-center gap-1">
                            <i class="fas fa-clock text-warning fs-8"></i> Menunggu
                        </span>';
            })
            ->addColumn('action', function ($row) {
                $status = strtolower($row->status ?? 'pending');
                $btnGroup = '<div class="btn-group">
                                <button type="button" class="btn btn-sm btn-light-primary dropdown-toggle px-3 py-2 fs-7 fw-semibold" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cog me-1"></i> Aksi
                                </button>
                                <ul class="dropdown-menu shadow-sm border-0 fs-7 rounded-3 p-2 min-w-175px">
                                    ' . ($status !== 'diproses' ? '<li><a class="dropdown-item py-2 px-3 rounded-2 btn-status" href="javascript:void(0)" data-id="' . $row->id . '" data-status="diproses"><i class="fas fa-spinner text-info me-2"></i>Tandai Diproses</a></li>' : '') . '
                                    ' . ($status !== 'selesai' ? '<li><a class="dropdown-item py-2 px-3 rounded-2 btn-status" href="javascript:void(0)" data-id="' . $row->id . '" data-status="selesai"><i class="fas fa-check-circle text-success me-2"></i>Tandai Selesai</a></li>' : '') . '
                                    ' . ($status !== 'pending' ? '<li><a class="dropdown-item py-2 px-3 rounded-2 btn-status" href="javascript:void(0)" data-id="' . $row->id . '" data-status="pending"><i class="fas fa-clock text-warning me-2"></i>Kembalikan Pending</a></li>' : '') . '
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li><a class="dropdown-item py-2 px-3 rounded-2 text-danger btn-delete" href="javascript:void(0)" data-id="' . $row->id . '"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Data</a></li>
                                </ul>
                            </div>';
                return '<div class="text-center">' . $btnGroup . '</div>';
            })
            ->rawColumns(['prodi_info', 'kontak_wa', 'status_badge', 'action'])
            ->make(true);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai',
        ]);

        $bantuan = BantuanLupaNim::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'Kaprodi' && (string) $bantuan->prodi_id !== (string) $user->prodi_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($user->role === 'Fakultas' && (string) $bantuan->fakultas_id !== (string) $user->fakultas_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $bantuan->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status permohonan berhasil diperbarui menjadi ' . ucfirst($request->status) . '.',
        ]);
    }

    public function destroy($id)
    {
        $bantuan = BantuanLupaNim::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'Kaprodi' && (string) $bantuan->prodi_id !== (string) $user->prodi_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($user->role === 'Fakultas' && (string) $bantuan->fakultas_id !== (string) $user->fakultas_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $bantuan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permohonan bantuan berhasil dihapus.',
        ]);
    }
}
