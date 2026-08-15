@extends('layouts.main')

@section('title', $nama)

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">

            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">
                    
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <h3 class="fw-bolder fs-2 m-0">{{ $nama }}</h3>
                        <a href="{{ route('admin.perusahaan.index') }}" class="btn btn-sm btn-light border">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                    
                    <div class="mb-8">
                        <span class="text-gray-600 fw-semibold">Detail PT / Instansi dan daftar mahasiswa yang terhubung melalui tracer study.</span>
                    </div>

                    <div class="row g-5 g-xl-8 mb-8">
                        <div class="col-xl-8">
                            <div class="card shadow-sm border border-dashed border-dark rounded h-100">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title text-gray-500 fw-bold fs-7 text-uppercase mb-0">Profil PT / Instansi</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="fw-bolder text-dark mb-8">{{ $nama }}</h2>
                                    
                                    <div class="row g-5">
                                        <div class="col-md-6">
                                            <div class="bg-light p-4 rounded border border-dashed">
                                                <div class="text-gray-500 fw-bold fs-7 mb-1">JENIS</div>
                                                <div class="text-dark fw-semibold fs-6">{{ $perusahaan->jenis_instansi ?: '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light p-4 rounded border border-dashed">
                                                <div class="text-gray-500 fw-bold fs-7 mb-1">LOKASI</div>
                                                <div class="text-dark fw-semibold fs-6">
                                                    @php
                                                        $loc = [];
                                                        if ($perusahaan->kabupaten) $loc[] = $perusahaan->kabupaten;
                                                        if ($perusahaan->provinsi) $loc[] = $perusahaan->provinsi;
                                                        echo count($loc) > 0 ? implode(', ', $loc) : '-';
                                                    @endphp
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card shadow-sm border border-dashed border-dark rounded h-100">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title text-gray-500 fw-bold fs-7 text-uppercase mb-0">Ringkasan Relasi</h3>
                                </div>
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-light-success p-6 rounded border border-success border-dashed w-100">
                                        <div class="text-success fw-bold fs-6 mb-2">TRACER STUDY</div>
                                        <div class="text-dark fw-bolder fs-1">{{ $pekerjaanList->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border border-dashed border-dark rounded">
                        <div class="card-header border-0 pt-6 pb-4">
                            <h3 class="card-title text-gray-500 fw-bold fs-7 text-uppercase mb-0">Daftar Mahasiswa</h3>
                            <div class="mt-2">
                                <span class="text-dark fw-bolder fs-5">Alumni yang terhubung ke PT / Instansi ini</span>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 mt-4">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="min-w-200px">Mahasiswa</th>
                                            <th class="min-w-150px">Prodi</th>
                                            <th class="text-end min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        @forelse($pekerjaanList as $p)
                                            @php
                                                $mhs = $p->responTracer->mahasiswa ?? null;
                                                $prodi = $mhs->prodi ?? null;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 text-hover-primary mb-1 fw-bolder text-uppercase">
                                                                {{ $mhs ? $mhs->nama_mahasiswa : '-' }}
                                                            </span>
                                                            <span class="text-muted fs-7">{{ $mhs ? $mhs->nim : '-' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-gray-600 fw-semibold">{{ $prodi ? $prodi->nama_prodi : '-' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    @if($p->responTracer)
                                                        <a href="{{ route('admin.respon.show', $p->responTracer->id_respon) }}" class="btn btn-sm btn-light border fw-bold text-dark">
                                                            Detail Tracer
                                                        </a>
                                                    @else
                                                        <span class="text-muted fs-7">Data tidak tersedia</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-5">
                                                    Tidak ada data mahasiswa.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>
@endsection
