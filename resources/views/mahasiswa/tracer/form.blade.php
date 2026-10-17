@extends('layouts.main')

@section('title', 'Form Tracer Study')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    @if (session('error'))
                        <div class="alert alert-danger p-5 mb-5">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger p-5 mb-5">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card shadow-sm border border-dashed border-primary rounded mb-5">
                        <div class="card-header pt-6 border-0">
                            <h3 class="card-title m-0 d-flex align-items-start">
                                <div class="me-4 d-flex justify-content-center align-items-center mt-1">
                                    <i class="fas fa-clipboard-list text-primary fs-1"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="card-label fw-bolder fs-2 m-0 mb-2">{{ $kuesioner->judul }}</span>
                                    <span class="text-muted fw-bold fs-7 d-flex align-items-center m-0">
                                        Target: {{ $kuesioner->target_lulusan_label ?? 'Semua Lulusan' }}
                                    </span>
                                </div>
                            </h3>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="card-body">
                            <form action="{{ route('mahasiswa.tracer.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="kuesioner_id" value="{{ $kuesioner->id_kuesioner }}">

                                @include('mahasiswa.tracer._fields')


                                <div class="separator mb-5"></div>
                                <div class="d-flex justify-content-end mb-5">
                                    <button type="button" id="btn_submit_tracer" class="btn btn-success btn-md w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Jawaban Tracer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
@endsection

@section('js')
    @include('mahasiswa.tracer._form_js')
@endsection
