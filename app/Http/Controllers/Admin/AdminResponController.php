<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Kuesioner;
use App\Models\Mahasiswa;
use App\Models\Pertanyaan;
use App\Models\Prodi;
use App\Models\ResponTracer;
use App\Services\TracerResponService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdminResponController extends Controller
{
    public function index()
    {
        $kuesioner = Kuesioner::orderByTahunTerbaru()->get();
        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $isKaprodi = $user && $user->role === 'Kaprodi';

        if ($isKaprodi) {
            $fakultas = collect();
            $prodi = Prodi::where('id_prodi', $user->prodi_id)->get();
        } elseif ($isFakultas) {
            $fakultas = Fakultas::where('id_fakultas', $user->fakultas_id)->get();
            $prodi = Prodi::where('fakultas_id', $user->fakultas_id)->orderBy('nama_prodi')->get();
        } else {
            $fakultas = Fakultas::orderBy('nama_fakultas')->get();
            $prodi = Prodi::orderBy('nama_prodi')->get();
        }

        return view('admin.respon.index', compact('kuesioner', 'fakultas', 'prodi', 'isFakultas', 'isKaprodi'));
    }

    public function getRespon(Request $request)
    {
        $query = ResponTracer::with(['mahasiswa.prodi.fakultas', 'kuesioner'])->select(['id_respon', 'mahasiswa_id', 'kuesioner_id', 'status', 'tgl_isi'])->orderByDesc('tgl_isi');

        if (auth()->user()->role === 'Kaprodi') {
            $nimList = Mahasiswa::where('prodi_id', auth()->user()->prodi_id)->pluck('nim')->toArray();
            $query->whereIn('mahasiswa_id', $nimList);
        } elseif (auth()->user()->role === 'Fakultas') {
            $facultyProdiIds = Prodi::where('fakultas_id', auth()->user()->fakultas_id)->pluck('id_prodi')->toArray();
            $nimList = Mahasiswa::whereIn('prodi_id', $facultyProdiIds)->pluck('nim')->toArray();
            $query->whereIn('mahasiswa_id', $nimList);
        } elseif ($request->filled('fakultas_id') && $request->fakultas_id !== 'all') {
            $facultyProdiIds = Prodi::where('fakultas_id', $request->fakultas_id)->pluck('id_prodi')->toArray();
            $nimList = Mahasiswa::whereIn('prodi_id', $facultyProdiIds)->pluck('nim')->toArray();
            $query->whereIn('mahasiswa_id', $nimList);
        }

        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $nimList = Mahasiswa::where('prodi_id', $request->prodi_id)->pluck('nim')->toArray();
            $query->whereIn('mahasiswa_id', $nimList);
        }

        if ($request->filled('kuesioner_id') && $request->kuesioner_id !== 'all') {
            $query->where('kuesioner_id', $request->kuesioner_id);
        }

        return DataTables::of($query)
            ->addColumn('mahasiswa_nama', function ($row) {
                return $row->mahasiswa ? $row->mahasiswa->nama : '-';
            })
            ->addColumn('mahasiswa_nim', function ($row) {
                return $row->mahasiswa_id;
            })
            ->addColumn('fakultas_nama', function ($row) {
                return $row->mahasiswa && $row->mahasiswa->prodi && $row->mahasiswa->prodi->fakultas ? $row->mahasiswa->prodi->fakultas->nama_fakultas : '-';
            })
            ->addColumn('prodi_nama', function ($row) {
                return $row->mahasiswa && $row->mahasiswa->prodi ? $row->mahasiswa->prodi->nama_prodi : '-';
            })
            ->addColumn('kuesioner_judul', function ($row) {
                return $row->kuesioner ? $row->kuesioner->judul : '-';
            })
            ->addColumn('tgl_isi_format', function ($row) {
                return $row->tgl_isi ? Carbon::parse($row->tgl_isi)->translatedFormat('d F Y, H:i') . ' WIB' : '-';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="' . route('admin.respon.show', $row->id_respon) . '"
                                class="btn btn-sm btn-light btn-active-light-info text-center"
                                data-bs-toggle="tooltip" title="Lihat Detail Jawaban">
                                <i class="fa fa-eye"></i> Detail
                            </a>';

                return '<div class="text-center">' . $showBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $query = ResponTracer::with([
            'mahasiswa.prodi.fakultas',
            'kuesioner.kategoriPertanyaans.pertanyaans',
            'jawabanDetails.pertanyaan',
            'pekerjaanAlumni'
        ]);

        if (auth()->user()->role === 'Kaprodi') {
            $query->whereHas('mahasiswa', function ($q) {
                $q->where('prodi_id', auth()->user()->prodi_id);
            });
        } elseif (auth()->user()->role === 'Fakultas') {
            $query->whereHas('mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        }

        $respon = $query->findOrFail($id);

        $jawabanUser = [];
        foreach ($respon->jawabanDetails as $detail) {
            $pertanyaan = $detail->pertanyaan;

            if ($detail->jawaban_json) {
                $decoded = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
                if (is_array($decoded) && isset($decoded['label'])) {
                    $jawabanUser[$detail->pertanyaan_id] = $decoded['label'];
                } else {
                    $jawabanUser[$detail->pertanyaan_id] = $decoded;
                }
            } else {
                $val = $detail->jawaban_text;
                if ($pertanyaan && $pertanyaan->tipe_jawaban === 'checkbox' && is_string($val)) {
                    $jawabanUser[$detail->pertanyaan_id] = array_map('trim', explode(',', $val));
                } else {
                    $jawabanUser[$detail->pertanyaan_id] = $val;
                }
            }
        }

        $pekerjaan = $respon->pekerjaanAlumni;
        $pertanyaans = Pertanyaan::whereIn('kode_pertanyaan', ['F5A0', 'F5A1', 'F5A2', 'f5a0', 'f5a1', 'f5a2', 'f18b', 'F18B'])->get();

        foreach ($pertanyaans as $p) {
            $id = $p->id_pertanyaan;
            $kode = strtolower($p->kode_pertanyaan);

            if ($kode === 'f5a0') {
                if (!empty($jawabanUser[$id]) && strlen($jawabanUser[$id]) == 2) {
                    $val = $jawabanUser[$id];
                    $negara = Cache::remember("master_negara_{$val}", 86400, function () use ($val) {
                        return DB::table('master_negara')->where('kode_wilayah_negara', $val)->value('negara');
                    });
                    if ($negara) $jawabanUser[$id] = $negara;
                }
            } elseif ($kode === 'f18b') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->nama;
                }
            } elseif ($kode === 'f5a1') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->provinsi ?? $pekerjaan->kode_provinsi;
                }
                if (!empty($jawabanUser[$id]) && preg_match('/^\d+$/', $jawabanUser[$id])) {
                    $val = $jawabanUser[$id];
                    $prov = Cache::remember("master_provinsi_{$val}", 86400, function () use ($val) {
                        return DB::table('master_provinsi')->where('kode_wilayah_provinsi', $val)->value('provinsi');
                    });
                    if ($prov) $jawabanUser[$id] = $prov;
                }
            } elseif ($kode === 'f5a2') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->kabupaten ?? $pekerjaan->kode_kabupaten;
                }
                if (!empty($jawabanUser[$id]) && preg_match('/^\d+$/', $jawabanUser[$id])) {
                    $val = $jawabanUser[$id];
                    $kab = Cache::remember("master_kab_{$val}", 86400, function () use ($val) {
                        return DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $val)->value('kota_kabupaten');
                    });
                    if ($kab) $jawabanUser[$id] = $kab;
                }
            }
        }

        $jsonKemdikbud = [];
        foreach ($respon->kuesioner->kategoriPertanyaans as $kategori) {
            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!empty($pertanyaan->kode_pertanyaan)) {
                    $val = $jawabanUser[$pertanyaan->id_pertanyaan] ?? null;
                    if ($val !== null && $val !== "") {
                        $jsonKemdikbud[$pertanyaan->kode_pertanyaan] = is_array($val) ? implode(", ", $val) : (string)$val;
                    }
                }
            }
        }

        return view('admin.respon.show', compact('respon', 'jawabanUser', 'jsonKemdikbud'));
    }

    public function create(Request $request)
    {
        $kuesionerList = Kuesioner::orderByTahunTerbaru()->get();

        $kuesionerAktif = null;
        $mahasiswaTarget = null;
        $jawabanUser = [];
        $jawabanLabel = [];

        if ($request->filled('kuesioner_id') && $request->filled('nim')) {
            $kuesionerAktif = Kuesioner::with(['kategoriPertanyaans' => function ($q) {
                $q->orderBy('urutan');
            }, 'kategoriPertanyaans.pertanyaans'])->findOrFail($request->kuesioner_id);

            $mahasiswaTarget = Mahasiswa::with('prodi.fakultas')
                ->where('nim', trim((string) $request->nim))
                ->firstOrFail();

            if ($this->isKaprodiScope() && !$this->inOwnProdi($mahasiswaTarget)) {
                abort(403, 'Mahasiswa tersebut bukan dari program studi Anda.');
            }

            if ($this->isFakultasScope() && !$this->inOwnFakultas($mahasiswaTarget)) {
                abort(403, 'Mahasiswa tersebut bukan dari fakultas Anda.');
            }

            if (!$this->akademikCocok($kuesionerAktif, $mahasiswaTarget)) {
                return redirect()->route('admin.respon.create', ['kuesioner_id' => $kuesionerAktif->id_kuesioner])
                    ->with('error', "Mahasiswa {$mahasiswaTarget->nim} ({$mahasiswaTarget->tahun_keluar_label}) tidak termasuk sasaran kuesioner ini ({$kuesionerAktif->target_lulusan_label}).");
            }

            [$jawabanUser, $jawabanLabel] = TracerResponService::formData($kuesionerAktif, $mahasiswaTarget->nim);
        }

        return view('admin.respon.create', compact(
            'kuesionerList',
            'kuesionerAktif',
            'mahasiswaTarget',
            'jawabanUser',
            'jawabanLabel'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner',
            'nim'          => 'required|exists:mahasiswa,nim',
        ]);

        $nim = trim((string) $request->nim);
        $mahasiswa = Mahasiswa::with('prodi')->where('nim', $nim)->firstOrFail();

        if ($this->isKaprodiScope() && !$this->inOwnProdi($mahasiswa)) {
            return back()->with('error', 'Mahasiswa tersebut bukan dari program studi Anda.');
        }

        if ($this->isFakultasScope() && !$this->inOwnFakultas($mahasiswa)) {
            return back()->with('error', 'Mahasiswa tersebut bukan dari fakultas Anda.');
        }

        $kuesioner = Kuesioner::findOrFail($request->kuesioner_id);
        if (!$this->akademikCocok($kuesioner, $mahasiswa)) {
            return back()->with('error', "Mahasiswa {$nim} ({$mahasiswa->tahun_keluar_label}) tidak termasuk sasaran kuesioner ini ({$kuesioner->target_lulusan_label}).");
        }

        $jawaban = $request->input('jawaban', []);
        $jawabanLabel = $request->input('jawaban_label', []);

        if (is_array($jawabanLabel)) {
            foreach ($jawabanLabel as $qId => $lbl) {
                if ((!isset($jawaban[$qId]) || $jawaban[$qId] === '' || $jawaban[$qId] === null) && !empty($lbl)) {
                    $jawaban[$qId] = is_string($lbl) ? trim($lbl) : $lbl;
                }
            }
        }

        $wajibErrors = TracerResponService::validateWajibJawaban($kuesioner, $jawaban, $jawabanLabel);
        if (!empty($wajibErrors)) {
            return back()
                ->with('error', 'Terdapat ' . count($wajibErrors) . ' pertanyaan wajib yang belum diisi. Mohon periksa kembali formulir.')
                ->with('wajib_errors', $wajibErrors)
                ->withErrors($wajibErrors)
                ->withInput();
        }

        try {
            TracerResponService::save(
                $request->kuesioner_id,
                $nim,
                $jawaban,
                $jawabanLabel
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.respon.index')
            ->with('success', "Jawaban atas nama {$mahasiswa->nama} ({$nim}) berhasil disimpan.");
    }

    public function mahasiswaSearch(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));

        $query = Mahasiswa::with('prodi')->select(['nim', 'nama', 'prodi_id']);

        if ($this->isKaprodiScope()) {
            $query->where('prodi_id', auth()->user()->prodi_id);
        } elseif ($this->isFakultasScope()) {
            $prodiIds = Prodi::where('fakultas_id', auth()->user()->fakultas_id)->pluck('id_prodi')->toArray();
            $query->whereIn('prodi_id', $prodiIds);
        }

        if ($request->filled('kuesioner_id')) {
            $kuesioner = Kuesioner::find($request->kuesioner_id);
            if ($kuesioner && !empty($kuesioner->akademik_id)) {
                $matchingIds = $kuesioner->getMatchingAkademikIds();
                $query->whereIn('akademik_id', $matchingIds);
            }
        }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('nim', 'like', "%{$keyword}%")
                  ->orWhere('nama', 'like', "%{$keyword}%");
            });
        }

        return response()->json(
            $query->orderBy('nim')->limit(20)->get()->map(function ($m) {
                $prodi = $m->prodi ? " ({$m->prodi->nama_prodi})" : '';
                return ['id' => $m->nim, 'text' => "{$m->nim} - {$m->nama}{$prodi}"];
            })->values()
        );
    }

    public function lookup(Request $request)
    {
        return TracerResponService::lookup($request);
    }

    private function isFakultasScope(): bool
    {
        return auth()->user() && auth()->user()->role === 'Fakultas';
    }

    private function isKaprodiScope(): bool
    {
        return auth()->user() && auth()->user()->role === 'Kaprodi';
    }

    private function inOwnFakultas(Mahasiswa $mahasiswa): bool
    {
        return $mahasiswa->prodi
            && (string) $mahasiswa->prodi->fakultas_id === (string) auth()->user()->fakultas_id;
    }

    private function inOwnProdi(Mahasiswa $mahasiswa): bool
    {
        return (string) $mahasiswa->prodi_id === (string) auth()->user()->prodi_id;
    }

    private function akademikCocok(Kuesioner $kuesioner, Mahasiswa $mahasiswa): bool
    {
        return $kuesioner->matchesMahasiswa($mahasiswa);
    }
}
