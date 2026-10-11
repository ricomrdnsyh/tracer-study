<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracer Study - Universitas Nurul Jadid (UNUJA)</title>
    <meta name="description"
        content="Portal Resmi Tracer Study Universitas Nurul Jadid (UNUJA). Penelusuran rekam jejak lulusan untuk peningkatan mutu pendidikan dan akreditasi program studi.">
    <meta name="author" content="Universitas Nurul Jadid">
    <meta name="robots" content="index, follow">
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/unuja.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Instrument Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        '2xs': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-blue-600 selection:text-white"
    style="background-image: linear-gradient(to right, rgba(203, 213, 225, 0.28) 1px, transparent 1px), linear-gradient(to bottom, rgba(203, 213, 225, 0.28) 1px, transparent 1px); background-size: 28px 28px;">

    <!-- 1. Top Notice Announcement -->
    <aside aria-label="Pengumuman Periode" class="bg-[#0b1e36] text-blue-100 text-xs sm:text-sm py-2 px-4 border-b border-blue-950/80">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-600 text-white shadow-xs">Periode Aktif</span>
                <span>Pelaporan Tracer Study Lulusan Tahun 2023 &amp; 2024 Sedang Berlangsung</span>
            </div>
            <a href="#sasaran" class="inline-flex items-center text-yellow-400 hover:text-yellow-300 font-medium underline underline-offset-4 transition-colors">
                Lihat Kriteria Sasaran &rarr;
            </a>
        </div>
    </aside>

    <!-- 2. Header & Sticky Navbar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3.5 group" aria-label="Beranda Tracer Study UNUJA">
                    <img src="{{ asset('assets/media/logos/unuja.png') }}" alt="Logo Universitas Nurul Jadid" class="h-11 w-auto object-contain transition-transform group-hover:scale-105" loading="eager">
                    <div class="flex flex-col">
                        <span class="text-base sm:text-lg font-bold tracking-tight text-slate-900 leading-none">
                            TRACER STUDY
                        </span>
                        <span class="text-xs font-semibold text-blue-600 tracking-wide mt-1">
                            Universitas Nurul Jadid
                        </span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#hero" class="hover:text-blue-600 transition-colors">Beranda</a>
                    <a href="#sasaran" class="hover:text-blue-600 transition-colors">Sasaran Alumni</a>
                    <a href="#alur" class="hover:text-blue-600 transition-colors">Alur Pengisian</a>
                    <a href="#manfaat" class="hover:text-blue-600 transition-colors">Manfaat</a>
                    <a href="#faq" class="hover:text-blue-600 transition-colors">Tanya Jawab</a>
                </nav>

                <!-- Action Buttons -->
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-700 hover:text-blue-700 hover:bg-blue-50/70 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-200 transition-all" title="Masuk sebagai Administrator / Fakultas">
                        Portal Staf &amp; Admin
                    </a>
                    <a href="{{ route('tracer.login') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2 text-xs font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl shadow-md shadow-blue-500/20 hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5" title="Isi Formulir Tracer Study">
                        <span>Isi Kuesioner</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex sm:hidden">
                    <button type="button" id="mobile-menu-btn" class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl focus:outline-hidden" aria-expanded="false" aria-label="Buka navigasi menu">
                        <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobile-menu" class="hidden sm:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-3">
            <nav class="flex flex-col space-y-2 text-sm font-medium text-slate-700">
                <a href="#hero" class="mobile-nav-link px-3 py-2 rounded-lg hover:bg-slate-50 hover:text-blue-600">Beranda</a>
                <a href="#sasaran" class="mobile-nav-link px-3 py-2 rounded-lg hover:bg-slate-50 hover:text-blue-600">Sasaran Alumni</a>
                <a href="#alur" class="mobile-nav-link px-3 py-2 rounded-lg hover:bg-slate-50 hover:text-blue-600">Alur Pengisian</a>
                <a href="#manfaat" class="mobile-nav-link px-3 py-2 rounded-lg hover:bg-slate-50 hover:text-blue-600">Manfaat</a>
                <a href="#faq" class="mobile-nav-link px-3 py-2 rounded-lg hover:bg-slate-50 hover:text-blue-600">Tanya Jawab</a>
            </nav>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                <a href="{{ route('tracer.login') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl shadow-xs text-center">
                    Isi Kuesioner Sekarang
                </a>
                <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl border border-slate-200 text-center">
                    Portal Staf &amp; Admin
                </a>
            </div>
        </div>
    </header>

    <main id="hero">
        <!-- 3. Hero Section (Split Interactive Showcase) -->
        <section class="relative pt-12 pb-20 lg:pt-16 lg:pb-28 overflow-hidden">
            <!-- Decorative Ambient Glows -->
            <div class="absolute -top-24 left-1/4 w-96 h-96 bg-blue-400/15 rounded-full blur-3xl pointer-events-none -z-10"></div>
            <div class="absolute top-28 right-10 w-[420px] h-[420px] bg-indigo-300/15 rounded-full blur-3xl pointer-events-none -z-10"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    
                    <!-- Left Column: Academic Pitch & CTA -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-blue-50/90 border border-blue-200/80 text-blue-700 text-xs font-semibold shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Instrumen Resmi Ditjen Diktiristek &amp; BAN-PT</span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-[1.18]">
                            Satu Langkah Nyata Alumni untuk <span class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 bg-clip-text text-transparent">Kemajuan Almamater</span>.
                        </h1>

                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl">
                            Tracer Study Universitas Nurul Jadid merupakan sarana resmi penelusuran rekam jejak, masa transisi karir, serta relevansi kurikulum pendidikan tinggi. Kontribusi data Anda adalah fondasi utama peningkatan akreditasi program studi dan pengembangan mutu lulusan UNUJA.
                        </p>

                        <!-- Call to Actions -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ route('tracer.login') }}" class="inline-flex items-center justify-center gap-3 px-7 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:bg-blue-800 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/35 transition-all transform hover:-translate-y-0.5">
                                <span>Mulai Pengisian Kuesioner</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                            <a href="#alur" class="inline-flex items-center justify-center px-6 py-3.5 text-sm font-semibold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50/80 rounded-xl border border-slate-200/90 shadow-2xs hover:border-slate-300 transition-colors">
                                Pelajari Alur &amp; Sasaran
                            </a>
                        </div>

                        <!-- Trust / Guidance Footnote -->
                        <div class="pt-3 flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-slate-500 font-medium">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Estimasi waktu: ~5–7 Menit</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Kerahasiaan terlindungi UU PDP</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                                <span>Masuk via NIM &amp; Tanggal Lahir</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Interactive Questionnaire Showcase Card -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative bg-white/95 backdrop-blur-md rounded-3xl border border-slate-200/90 shadow-2xl shadow-blue-900/10 p-6 sm:p-7 overflow-hidden transition-all hover:border-blue-300">
                            
                            <!-- Card Header: Window Controls & Badge -->
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                    </div>
                                    <span class="ml-2 text-xs font-bold text-slate-700 tracking-tight">Simulasi Pengisian Kuesioner</span>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    Tahun Pelaporan 2026
                                </span>
                            </div>

                            <!-- Step Progress Indicator -->
                            <div class="py-4 space-y-1.5">
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                    <span>Langkah 1 dari 4: Identifikasi Lulusan</span>
                                    <span class="text-blue-600 font-bold">25% Selesai</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full w-1/4 animate-pulse"></div>
                                </div>
                            </div>

                            <!-- Interactive Simulated Form Fields -->
                            <div class="space-y-3.5 py-2">
                                <!-- NIM Field Mock -->
                                <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200 space-y-1">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Nomor Induk Mahasiswa (NIM)</label>
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-sm font-bold text-slate-900 tracking-wider">20205100***</span>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Terdaftar di PDDikti
                                        </span>
                                    </div>
                                </div>

                                <!-- Tanggal Lahir Field Mock -->
                                <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200 space-y-1">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Tanggal Lahir Responden</label>
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-sm font-semibold text-slate-700">12 / 08 / 2001</span>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500">
                                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Verifikasi Otentik
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Highlights Bar -->
                            <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] font-medium text-slate-600">
                                <div class="flex items-center gap-1.5 p-2 rounded-lg bg-blue-50/60 border border-blue-100">
                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Autosave Aktif</span>
                                </div>
                                <div class="flex items-center gap-1.5 p-2 rounded-lg bg-emerald-50/60 border border-emerald-100">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span>Enkripsi SSL 256-bit</span>
                                </div>
                            </div>

                            <!-- Direct Action Button inside Showcase -->
                            <a href="{{ route('tracer.login') }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-xs font-bold uppercase tracking-wider text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200/90 rounded-xl transition-all hover:shadow-xs">
                                <span>Akses Formulir Pengisian Resmi</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- 4. Key Metrics Ribbon -->
        <section class="border-y border-slate-200/80 bg-white/80 backdrop-blur-xs py-10" aria-label="Ringkasan Statistik Institusi">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                    <!-- Metric 1 -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs hover:border-blue-300 transition-all text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Fakultas</span>
                        </div>
                        <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">4 Fakultas</div>
                        <p class="text-xs text-slate-500 font-medium mt-1">FAI, FT, FKes, &amp; FSH UNUJA</p>
                    </div>

                    <!-- Metric 2 -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs hover:border-blue-300 transition-all text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Program Studi</span>
                        </div>
                        <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">15+ Prodi</div>
                        <p class="text-xs text-slate-500 font-medium mt-1">Terakreditasi BAN-PT &amp; LAM</p>
                    </div>

                    <!-- Metric 3 -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs hover:border-blue-300 transition-all text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Mutu</span>
                        </div>
                        <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Standar IKU-1</div>
                        <p class="text-xs text-slate-500 font-medium mt-1">Kesiapan Kerja &amp; Karir Lulusan</p>
                    </div>

                    <!-- Metric 4 -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs hover:border-blue-300 transition-all text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Keamanan Data</span>
                        </div>
                        <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">100% Aman</div>
                        <p class="text-xs text-slate-500 font-medium mt-1">Anonimitas &amp; Perlindungan UU PDP</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Sasaran & Kriteria Alumni (Target Cohort) - Sleek SVG Badges (No Emojis) -->
        <section id="sasaran" class="py-16 sm:py-24 border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12 sm:mb-16">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600">Kriteria Responden</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Siapa yang Wajib Mengisi Tracer Study?
                    </h2>
                    <p class="text-slate-600 mt-3 text-base leading-relaxed">
                        Sesuai pedoman pelaporan Dikti, seluruh lulusan pada kohort sasaran diharapkan berpartisipasi aktif apapun status transisi aktivitas yang sedang dijalani saat ini.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    <!-- Kohort 1: Bekerja -->
                    <div class="bg-white p-7 rounded-3xl border border-slate-200/90 shadow-2xs hover:border-blue-300 hover:shadow-md hover:-translate-y-1 transition-all group">
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200/80">
                                Kategori 01
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Alumni yang Sedang Bekerja</h3>
                        <p class="text-sm text-slate-600 mt-2.5 leading-relaxed">
                            Bekerja di instansi pemerintah, BUMN, perusahaan swasta, yayasan, organisasi non-profit, maupun lembaga pendidikan formal/non-formal.
                        </p>
                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-blue-600">
                            <span>Fokus: Keselarasan bidang studi</span>
                            <span class="text-slate-400">&rarr;</span>
                        </div>
                    </div>

                    <!-- Kohort 2: Wirausaha -->
                    <div class="bg-white p-7 rounded-3xl border border-slate-200/90 shadow-2xs hover:border-blue-300 hover:shadow-md hover:-translate-y-1 transition-all group">
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                                Kategori 02
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Alumni Berwirausaha</h3>
                        <p class="text-sm text-slate-600 mt-2.5 leading-relaxed">
                            Mendirikan usaha mandiri, freelance profesional, startup rintisan, atau mengelola usaha keluarga di berbagai sektor ekonomi produktif.
                        </p>
                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-indigo-600">
                            <span>Fokus: Skala usaha &amp; kemandirian</span>
                            <span class="text-slate-400">&rarr;</span>
                        </div>
                    </div>

                    <!-- Kohort 3: Studi Lanjut / Transisi -->
                    <div class="bg-white p-7 rounded-3xl border border-slate-200/90 shadow-2xs hover:border-blue-300 hover:shadow-md hover:-translate-y-1 transition-all group">
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                Kategori 03
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Studi Lanjut &amp; Mencari Peluang</h3>
                        <p class="text-sm text-slate-600 mt-2.5 leading-relaxed">
                            Melanjutkan jenjang magister/doktoral, mengikuti sertifikasi profesi, atau yang sedang aktif mencari peluang karir pertama.
                        </p>
                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-600">
                            <span>Fokus: Linieritas studi &amp; transisi karir</span>
                            <span class="text-slate-400">&rarr;</span>
                        </div>
                    </div>
                </div>

                <!-- Equal Value Callout Banner -->
                <div class="mt-8 p-5 rounded-2xl bg-white border border-blue-200/80 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-1.5 rounded-full bg-blue-600 text-white mt-0.5 shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-medium">
                            <strong class="text-slate-900">Catatan Penting:</strong> Seluruh status aktivitas alumni memiliki bobot nilai yang setara dalam evaluasi mutu pendidikan kampus. Tidak ada penilaian benar atau salah.
                        </p>
                    </div>
                    <a href="{{ route('tracer.login') }}" class="shrink-0 text-xs font-bold text-blue-600 hover:text-blue-800 underline underline-offset-4">
                        Isi Kuesioner Sekarang &rarr;
                    </a>
                </div>
            </div>
        </section>

        <!-- 6. Alur 4 Langkah Pengisian (Connected Process Roadmap) -->
        <section id="alur" class="py-16 sm:py-24 bg-white/90 backdrop-blur-xs border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600">Tahapan Sederhana</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Alur Pengisian Kuesioner Tracer Study
                    </h2>
                    <p class="text-slate-600 mt-3 text-base leading-relaxed">
                        Hanya butuh 4 langkah ringkas untuk menyelesaikan partisipasi Anda. Seluruh proses dirancang efisien dan mudah diakses dari perangkat apapun.
                    </p>
                </div>

                <!-- Process Steps Grid with Connection Flow -->
                <div class="relative grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Step 1 -->
                    <div class="relative bg-slate-50/80 p-6 rounded-3xl border border-slate-200/90 flex flex-col hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-sm flex items-center justify-center shadow-md shadow-blue-500/20">01</span>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Langkah 1</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Akses &amp; Identifikasi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed flex-1">
                            Buka formulir tracer study, lalu masukkan <strong>NIM</strong> dan <strong>Tanggal Lahir</strong> untuk verifikasi data alumni terdaftar.
                        </p>
                        <div class="mt-4 pt-3 border-t border-slate-200 text-xs font-semibold text-blue-600">
                            Tanpa registrasi akun baru
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative bg-slate-50/80 p-6 rounded-3xl border border-slate-200/90 flex flex-col hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-sm flex items-center justify-center shadow-md shadow-blue-500/20">02</span>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Langkah 2</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Verifikasi Biodata</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed flex-1">
                            Periksa nama lengkap, program studi, tahun kelulusan, dan perbarui nomor kontak atau email aktif Anda untuk korespondensi resmi.
                        </p>
                        <div class="mt-4 pt-3 border-t border-slate-200 text-xs font-semibold text-blue-600">
                            Sinkronisasi data otomatis
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative bg-slate-50/80 p-6 rounded-3xl border border-slate-200/90 flex flex-col hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-sm flex items-center justify-center shadow-md shadow-blue-500/20">03</span>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Langkah 3</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Isi Riwayat Transisi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed flex-1">
                            Jawab kuesioner mengenai masa tunggu kerja, relevansi kompetensi mata kuliah, pengalaman kerja, atau kendala dalam transisi karir.
                        </p>
                        <div class="mt-4 pt-3 border-t border-slate-200 text-xs font-semibold text-blue-600">
                            Format pilihan ganda &amp; singkat
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative bg-slate-50/80 p-6 rounded-3xl border border-slate-200/90 flex flex-col hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-sm flex items-center justify-center shadow-md shadow-blue-500/20">04</span>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Langkah 4</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Kirim &amp; Selesai</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed flex-1">
                            Simpan jawaban akhir. Sistem mencatat partisipasi Anda secara resmi sebagai bagian dari pemenuhan instrumen akreditasi prodi.
                        </p>
                        <div class="mt-4 pt-3 border-t border-slate-200 text-xs font-semibold text-blue-600">
                            Tercatat langsung di PDSI
                        </div>
                    </div>
                </div>

                <!-- CTA Banner Under Steps -->
                <div class="mt-14 text-center">
                    <a href="{{ route('tracer.login') }}" class="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                        <span>Mulai Langkah Pertama Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- 7. Tiga Nilai Manfaat Nyata (Institutional Impact) -->
        <section id="manfaat" class="py-16 sm:py-24 border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12 sm:mb-16">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600">Dampak Nyata</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Mengapa Kontribusi Anda Sangat Krusial?
                    </h2>
                    <p class="text-slate-600 mt-3 text-base leading-relaxed">
                        Data tracer study bukanlah sekadar formalitas pengisian borang, melainkan instrumen ilmiah yang berdampak langsung pada nilai akreditasi almamater dan adik tingkat Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Pillar 1: Akreditasi -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200/90 shadow-2xs hover:border-blue-300 hover:shadow-md transition-all space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200/80">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Peringkat Akreditasi Unggul</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Respon rate tracer study adalah indikator penilaian wajib BAN-PT dan Lembaga Akreditasi Mandiri (LAM). Semakin tinggi tingkat partisipasi alumni, semakin kuat pengakuan mutu ijazah lulusan UNUJA secara nasional.
                        </p>
                    </div>

                    <!-- Pillar 2: Kurikulum -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200/90 shadow-2xs hover:border-blue-300 hover:shadow-md transition-all space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200/80">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Pemutakhiran Kurikulum Praktis</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Umpan balik Anda mengenai kompetensi yang paling relevan di tempat kerja digunakan oleh para dosen dan dekanat untuk meremajakan kurikulum agar mahasiswa senantiasa adaptif terhadap kebutuhan industri modern.
                        </p>
                    </div>

                    <!-- Pillar 3: Jejaring Karir -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200/90 shadow-2xs hover:border-blue-300 hover:shadow-md transition-all space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200/80">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Penguatan Sinergi Alumni</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Membangun basis data jejaring alumni yang kuat di berbagai sektor industri di Indonesia, membuka peluang kolaborasi profesional, magang bagi mahasiswa aktif, serta kemitraan riset kampus dengan dunia usaha.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. Komitmen Jaminan Privasi Data (Trust & Privacy Box) -->
        <section class="py-12 bg-white/60 border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl p-8 sm:p-10 text-white relative overflow-hidden shadow-xl" style="background: linear-gradient(160deg, #0f2744, #1e3a5f 50%, #1e40af);">
                    <div class="max-w-3xl space-y-4">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 border border-white/20 text-yellow-400 text-xs font-semibold">
                            <span>Jaminan Perlindungan Data Pribadi (UU No. 27/2022)</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight">
                            Kerahasiaan Jawaban Anda Adalah Prioritas Tertinggi Kami.
                        </h3>
                        <p class="text-sm text-white/80 leading-relaxed">
                            Setiap informasi yang Anda masukkan dikelola dengan enkripsi aman oleh Pusat Data &amp; Sistem Informasi (PDSI) Universitas Nurul Jadid. Data hanya diproses secara agregat statistik untuk keperluan akreditasi Dikti dan tidak akan disebarluaskan kepada pihak ketiga manapun untuk tujuan komersial.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 9. FAQ Accordion (Tanya Jawab Terstruktur) -->
        <section id="faq" class="py-16 sm:py-24 border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                    
                    <!-- Left Side: Header & Helpdesk Card -->
                    <div class="lg:col-span-4 space-y-6">
                        <div>
                            <span class="inline-block px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold uppercase tracking-wider mb-3">
                                FAQ
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                Pertanyaan Umum
                            </h2>
                            <p class="text-slate-600 mt-3 text-sm leading-relaxed">
                                Punya pertanyaan seputar pengisian kuesioner Tracer Study? Temukan jawaban untuk pertanyaan yang paling sering diajukan oleh alumni di bawah ini.
                            </p>
                        </div>

                        <!-- Masih Butuh Bantuan Box -->
                        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-4">
                            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200/80">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Masih butuh bantuan?</h3>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                                    Jika jawaban yang Anda cari tidak ada, silakan hubungi tim helpdesk PDSI kami untuk bantuan lebih lanjut.
                                </p>
                            </div>
                            <a href="mailto:tracer@unuja.ac.id" class="inline-flex items-center justify-center w-full px-4 py-3 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors shadow-2xs">
                                Hubungi Kami via Email
                            </a>
                        </div>
                    </div>

                    <!-- Right Side: Accordion List -->
                    <div class="lg:col-span-8 space-y-3.5" id="faq-accordion">
                        <!-- FAQ 1 -->
                        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden transition-all">
                            <button type="button" class="faq-btn w-full px-6 py-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors focus:outline-hidden" aria-expanded="false">
                                <span class="text-sm sm:text-base">Apakah alumni yang belum bekerja tetap wajib mengisi?</span>
                                <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="faq-content hidden px-6 pb-5 pt-1 text-sm text-slate-600 leading-relaxed border-t border-slate-100">
                                Ya, sangat wajib. Informasi mengenai alumni yang sedang dalam masa transisi, mempersiapkan diri untuk studi lanjut, atau masih mencari peluang karir sangat bernilai bagi universitas untuk memetakan kendala riil lulusan dan memperbaiki program pembekalan karir di kampus.
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden transition-all">
                            <button type="button" class="faq-btn w-full px-6 py-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors focus:outline-hidden" aria-expanded="false">
                                <span class="text-sm sm:text-base">Kredensial apa yang digunakan untuk masuk ke formulir?</span>
                                <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="faq-content hidden px-6 pb-5 pt-1 text-sm text-slate-600 leading-relaxed border-t border-slate-100">
                                Anda cukup memasukkan Nomor Induk Mahasiswa (NIM) saat kuliah dan Tanggal Lahir Anda sesuai data yang terdaftar pada sistem akademik. Anda tidak perlu membuat akun baru.
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden transition-all">
                            <button type="button" class="faq-btn w-full px-6 py-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors focus:outline-hidden" aria-expanded="false">
                                <span class="text-sm sm:text-base">Berapa lama waktu yang dibutuhkan untuk mengisi kuesioner?</span>
                                <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="faq-content hidden px-6 pb-5 pt-1 text-sm text-slate-600 leading-relaxed border-t border-slate-100">
                                Rata-rata waktu pengisian adalah antara 5 hingga 7 menit. Pertanyaan disajikan dalam bentuk pilihan ganda yang intuitif. Jika pengisian terputus, data yang telah diisi sebelumnya akan tetap tersimpan otomatis.
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden transition-all">
                            <button type="button" class="faq-btn w-full px-6 py-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors focus:outline-hidden" aria-expanded="false">
                                <span class="text-sm sm:text-base">Bagaimana jika NIM saya tidak ditemukan atau lupa?</span>
                                <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="faq-content hidden px-6 pb-5 pt-1 text-sm text-slate-600 leading-relaxed border-t border-slate-100">
                                Silakan periksa nomor ijazah/transkrip nilai Anda, atau hubungi Pusat Data &amp; Sistem Informasi (PDSI) / Biro Kemahasiswaan UNUJA melalui kontak helpdesk di bagian bawah halaman ini dengan menyertakan nama lengkap, program studi, dan tahun kelulusan.
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden transition-all">
                            <button type="button" class="faq-btn w-full px-6 py-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors focus:outline-hidden" aria-expanded="false">
                                <span class="text-sm sm:text-base">Apakah data pribadi saya akan dipublikasikan secara umum?</span>
                                <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="faq-content hidden px-6 pb-5 pt-1 text-sm text-slate-600 leading-relaxed border-t border-slate-100">
                                Tidak. Seluruh data identitas personal, instansi bekerja, dan rentang penghasilan sepenuhnya dianonimkan dan dilindungi. Laporan yang dipublikasikan hanya berbentuk rekapitulasi statistik agregat (misal: persentase keselarasan bidang atau rata-rata waktu tunggu lulusan).
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- 10. Final Call to Action -->
        <section class="py-16 sm:py-20 text-white relative" style="background: linear-gradient(160deg, #0f2744, #1e3a5f 50%, #1e40af);">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-semibold bg-white/10 border border-white/20 text-yellow-400">
                    Mari Berkontribusi Hari Ini
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                    Suara Anda Membantu Menentukan Masa Depan Almamater.
                </h2>
                <p class="text-white/80 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                    Setiap detik waktu yang Anda luangkan menjadi pijakan kokoh bagi kemajuan ribuan mahasiswa aktif Universitas Nurul Jadid.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('tracer.login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-sm font-bold text-slate-900 bg-white hover:bg-yellow-400 rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5">
                        <span>Isi Kuesioner Sekarang</span>
                        <svg class="w-4 h-4 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-4 text-sm font-semibold text-white hover:text-yellow-400 rounded-xl border border-white/20 hover:bg-white/10 transition-colors">
                        Login Pengelola Staf &amp; Admin
                    </a>
                </div>
            </div>
        </section>
    </main>


    <!-- 11. Institutional Footer (Sesuai Desain Web E-Lapor UNUJA) -->
    <footer style="background: linear-gradient(160deg, #0f2744, #1e3a5f 50%, #1e40af);">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-16 pb-12">
            <div class="absolute top-0 left-0 right-0 h-px"
                style="background: linear-gradient(90deg, transparent, rgba(59,130,246,0.4), transparent);"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">

                <div class="sm:col-span-2 lg:col-span-4">
                    <div class="mb-6">
                        <a href="{{ route('home') }}" class="inline-block">
                            <img src="{{ asset('assets/media/logos/unuja.png') }}" alt="Logo Universitas Nurul Jadid"
                                class="h-10 lg:h-[50px] w-auto object-contain hover:opacity-90 active:opacity-90 transition-opacity active:scale-[0.98]">
                        </a>
                    </div>
                    <p class="text-white/50 text-sm leading-relaxed max-w-xs">
                        Portal resmi Tracer Study Universitas Nurul Jadid untuk penelusuran rekam jejak lulusan, peningkatan mutu pendidikan, dan akreditasi program studi.
                    </p>
                    <div class="flex gap-2.5 mt-6">
                        <a href="https://www.facebook.com/universitasnuruljadid/" target="_blank"
                            class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:bg-white/10 active:bg-white/10 hover:text-yellow-400 active:text-yellow-400 transition-all no-underline active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/unujaofficial/" target="_blank"
                            class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:bg-white/10 active:bg-white/10 hover:text-yellow-400 active:text-yellow-400 transition-all no-underline active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" />
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                            </svg>
                        </a>
                        <a href="https://x.com/unujaofficial" target="_blank"
                            class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:bg-white/10 active:bg-white/10 hover:text-yellow-400 active:text-yellow-400 transition-all no-underline active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                        <a href="https://www.tiktok.com/@unujaofficial" target="_blank"
                            class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:bg-white/10 active:bg-white/10 hover:text-yellow-400 active:text-yellow-400 transition-all no-underline active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 0 006.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.79 1.54V6.78a4.85 4.85 0 01-1.02-.09z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-3 lg:col-start-5">
                    <h4 class="font-bold text-white/80 text-xs uppercase tracking-[0.15em] mb-4">Kontak</h4>
                    <div class="w-8 h-0.5 rounded-full mb-5 bg-yellow-400"></div>
                    <ul class="flex flex-col gap-3.5 text-sm text-white/50 list-none p-0 m-0">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-400/70" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="leading-relaxed hover:text-yellow-400 active:text-yellow-400 transition-colors cursor-default active:scale-[0.98]">JL. PP
                                Nurul Jadid, Dusun Tj. Lor, Karanganyar, Kec. Paiton, Kabupaten Probolinggo, Jawa Timur
                                67291</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-blue-400/70" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <a href="tel:+628883077077"
                                class="text-white/50 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">0888 30 77077</a>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-blue-400/70" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 6 2 18 2 18 9" />
                                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                                <rect x="6" y="14" width="12" height="8" />
                            </svg>
                            <span class="hover:text-yellow-400 active:text-yellow-400 transition-colors cursor-default active:scale-[0.98]">Fax 0888 30 77077</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-blue-400/70" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:tracer@unuja.ac.id"
                                class="text-white/50 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">tracer@unuja.ac.id</a>
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h4 class="font-bold text-white/80 text-xs uppercase tracking-[0.15em] mb-4">Internal</h4>
                    <div class="w-8 h-0.5 rounded-full mb-5 bg-yellow-400"></div>
                    <ul class="flex flex-col gap-2.5 text-sm list-none p-0 m-0">
                        @foreach ([['url' => 'https://unuja.ac.id', 'label' => 'Universitas Nurul Jadid'], ['url' => 'https://pmb.unuja.ac.id', 'label' => 'PMB Universitas Nurul Jadid'], ['url' => 'https://sso.unuja.ac.id', 'label' => 'Portal SSO Universitas Nurul Jadid']] as $link)
                            <li><a href="{{ $link['url'] }}" target="_blank"
                                    class="inline-flex items-center gap-2 text-white/50 no-underline hover:text-yellow-400 active:text-yellow-400 hover:translate-x-1.5 active:translate-x-1.5 transition-all duration-300 group active:scale-[0.98]">
                                    <svg class="w-3.5 h-3.5 text-blue-400/70 group-hover:text-yellow-400 group-active:text-yellow-400 transition-colors shrink-0 active:scale-[0.98]"
                                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{ $link['label'] }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <h4 class="font-bold text-white/80 text-xs uppercase tracking-[0.15em] mb-4">Navigasi</h4>
                    <div class="w-8 h-0.5 rounded-full mb-5 bg-yellow-400"></div>
                    <ul class="flex flex-col gap-2.5 text-sm list-none p-0 m-0">
                        @foreach ([['url' => '#hero', 'label' => 'Beranda'], ['url' => '#sasaran', 'label' => 'Sasaran Alumni'], ['url' => '#alur', 'label' => 'Alur Pengisian'], ['url' => '#manfaat', 'label' => 'Manfaat'], ['url' => '#faq', 'label' => 'FAQ / Bantuan']] as $link)
                            <li><a href="{{ $link['url'] }}"
                                    class="inline-flex items-center gap-2 text-white/50 no-underline hover:text-yellow-400 active:text-yellow-400 hover:translate-x-1.5 active:translate-x-1.5 transition-all duration-300 group active:scale-[0.98]">
                                    <svg class="w-3.5 h-3.5 text-blue-400/70 group-hover:text-yellow-400 group-active:text-yellow-400 transition-colors shrink-0 active:scale-[0.98]"
                                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{ $link['label'] }}
                                </a></li>
                        @endforeach
                        <li><a href="{{ route('login') }}"
                                class="inline-flex items-center gap-2 text-white/50 no-underline hover:text-yellow-400 active:text-yellow-400 hover:translate-x-1.5 active:translate-x-1.5 transition-all duration-300 group active:scale-[0.98]">
                                <svg class="w-3.5 h-3.5 text-blue-400/70 group-hover:text-yellow-400 group-active:text-yellow-400 transition-colors shrink-0 active:scale-[0.98]"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                Portal Admin
                            </a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-sm text-white/30 m-0 font-medium">&copy; {{ date('Y') }} PDSI Universitas Nurul Jadid.
                    Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-4 text-sm text-white/30">
                    <a href="#faq" class="text-white/30 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">Kebijakan
                        Privasi</a>
                    <span class="w-1 h-1 rounded-full bg-white/10"></span>
                    <a href="#alur" class="text-white/30 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">Syarat
                        &amp; Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Vanilla JS (Accordion & Mobile Drawer) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile Menu Toggle
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('menu-icon-open');
            const iconClose = document.getElementById('menu-icon-close');

            if (mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', function () {
                    const isExpanded = mobileBtn.getAttribute('aria-expanded') === 'true';
                    mobileBtn.setAttribute('aria-expanded', !isExpanded);
                    mobileMenu.classList.toggle('hidden');
                    iconOpen.classList.toggle('hidden');
                    iconClose.classList.toggle('hidden');
                });

                // Auto close mobile drawer when any link is clicked
                document.querySelectorAll('.mobile-nav-link').forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        mobileBtn.setAttribute('aria-expanded', 'false');
                        iconOpen.classList.remove('hidden');
                        iconClose.classList.add('hidden');
                    });
                });
            }

            // FAQ Accordion
            const faqButtons = document.querySelectorAll('.faq-btn');
            faqButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const content = this.nextElementSibling;
                    const icon = this.querySelector('.faq-icon');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';

                    // Close all others
                    faqButtons.forEach(otherBtn => {
                        if (otherBtn !== this) {
                            otherBtn.setAttribute('aria-expanded', 'false');
                            otherBtn.nextElementSibling.classList.add('hidden');
                            const otherIcon = otherBtn.querySelector('.faq-icon');
                            if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                        }
                    });

                    // Toggle current
                    if (isExpanded) {
                        this.setAttribute('aria-expanded', 'false');
                        content.classList.add('hidden');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    } else {
                        this.setAttribute('aria-expanded', 'true');
                        content.classList.remove('hidden');
                        if (icon) icon.style.transform = 'rotate(180deg)';
                    }
                });
            });
        });
    </script>
</body>

</html>
