<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracer Study | Universitas Nurul Jadid</title>
    <meta name="description"
        content="Portal Resmi Tracer Study Universitas Nurul Jadid (UNUJA). Penelusuran rekam jejak lulusan untuk peningkatan mutu pendidikan dan akreditasi program studi.">
    <meta name="author" content="Universitas Nurul Jadid">
    <meta name="robots" content="index, follow">
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/unuja.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            950: '#0f2744',
                            900: '#1e3a5f',
                            800: '#274b73',
                            700: '#1e40af',
                        }
                    },
                    animation: {
                        'float': 'float 7s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-14px)'
                            },
                        },
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .text-gradient {
            background: linear-gradient(92deg, #60A5FA 0%, #A78BFA 55%, #FBBF24 110%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .text-gradient-light {
            background: linear-gradient(92deg, #1d4ed8 0%, #4f46e5 55%, #d97706 110%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .glass {
            background: rgba(255, 255, 255, .06);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .grid-bg {
            background-image: linear-gradient(rgba(148, 163, 184, .09) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, .09) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .7s cubic-bezier(.22, 1, .36, 1), transform .7s cubic-bezier(.22, 1, .36, 1);
        }

        .reveal.visible {
            opacity: 1;
            transform: none;
        }

        .reveal-d1 {
            transition-delay: .1s;
        }

        .reveal-d2 {
            transition-delay: .2s;
        }

        .faq-content {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows .35s cubic-bezier(.22, 1, .36, 1);
        }

        .faq-item.open .faq-content {
            grid-template-rows: 1fr;
        }

        .faq-inner {
            overflow: hidden;
        }

        .faq-item.open .faq-chevron {
            transform: rotate(45deg);
            background: #1d4ed8;
        }

        .faq-chevron {
            transition: transform .3s ease, background .3s ease;
        }

        .hero-bar {
            transform-origin: bottom;
            animation: growBar 1.1s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes growBar {
            from {
                transform: scaleY(0);
            }

            to {
                transform: scaleY(1);
            }
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }
    </style>
</head>

<body class="bg-white text-slate-800 font-sans antialiased">

    <div id="hero" class="relative z-40 scroll-mt-0">
        <div id="announcement"
            class="relative z-40 text-white text-xs sm:text-[13px] bg-navy-900 border-b border-blue-950/40"
            style="background-color:#1e3a5f">
            <div class="max-w-7xl mx-auto px-4 py-2 sm:py-2.5 flex items-center justify-center text-center">
                <p class="w-full font-medium text-slate-300 text-center leading-relaxed">
                    <span class="relative inline-flex h-2 w-2 shrink-0 align-middle mr-2 -translate-y-px">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                    </span>
                    @php
                        $activeList = $kuesionerAktifList ?? (isset($kuesionerAktif) && $kuesionerAktif ? collect([$kuesionerAktif]) : collect());
                    @endphp
                    @if ($activeList->isNotEmpty())
                        @if ($activeList->count() === 1)
                            Periode pelaporan <strong
                                class="text-white">{{ $activeList->first()->target_lulusan_label ?? ($activeList->first()->tahunAkademik->nm_smt ?? $activeList->first()->judul) }}</strong>
                            sedang berlangsung
                        @else
                            @php
                                $labels = $activeList->map(function ($k) {
                                    return $k->target_lulusan_label ?? ($k->tahunAkademik->nm_smt ?? $k->judul);
                                })->unique()->values();

                                if ($labels->count() === 1) {
                                    $labelText = $labels->first();
                                } else {
                                    $lastLabel = $labels->pop();
                                    $labelText = $labels->implode(', ') . ' & ' . $lastLabel;
                                }
                            @endphp
                            Periode pelaporan <strong class="text-white">{{ $labelText }}</strong> sedang berlangsung
                        @endif
                    @else
                        Periode pelaporan kuesioner <strong class="text-white">belum dibuka</strong> — pantau informasi
                        terbaru
                    @endif
                </p>
            </div>
        </div>
    </div>


    <header id="navbar"
        class="sticky top-0 z-50 transition-all duration-300 bg-white/90 backdrop-blur-xl border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 h-[72px]">
                <a href="{{ route('home') }}" class="flex items-center group" aria-label="Beranda Tracer Study UNUJA">
                    <img src="{{ asset('assets/media/logos/tracer-dark.png') }}"
                        alt="Tracer Study Universitas Nurul Jadid"
                        class="h-11 w-auto object-contain transition-transform group-hover:scale-[1.02]"
                        loading="eager">
                </a>

                <div class="hidden lg:flex items-center gap-1 ml-auto">
                    <nav class="flex items-center gap-1 text-sm font-semibold text-slate-600">
                        <a href="#hero"
                            class="px-4 py-2 rounded-full hover:text-blue-700 hover:bg-blue-50 transition-all">Beranda</a>
                        <a href="#tentang"
                            class="px-4 py-2 rounded-full hover:text-blue-700 hover:bg-blue-50 transition-all">Tentang</a>
                        <a href="#alur"
                            class="px-4 py-2 rounded-full hover:text-blue-700 hover:bg-blue-50 transition-all">Alur</a>
                        <a href="#faq"
                            class="px-4 py-2 rounded-full hover:text-blue-700 hover:bg-blue-50 transition-all">FAQ</a>
                        <a href="{{ route('tracer.login') }}"
                            class="px-4 py-2 rounded-full hover:text-blue-700 hover:bg-blue-50 transition-all">Isi
                            Kuesioner</a>
                    </nav>
                    <span class="w-px h-6 bg-slate-200 mx-2"></span>
                    <a href="https://sso.unuja.ac.id"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-xl bg-navy-900 hover:bg-blue-700 shadow-lg shadow-navy-900/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
                        </svg>
                        Login
                    </a>
                </div>

                <div class="hidden sm:flex lg:hidden items-center gap-2.5 ml-auto">
                    <a href="https://sso.unuja.ac.id"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-xl bg-navy-900 hover:bg-blue-700 shadow-lg shadow-navy-900/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
                        </svg>
                        Login
                    </a>
                </div>

                <button id="mobile-menu-btn"
                    class="lg:hidden ml-auto sm:ml-2 p-2.5 rounded-xl text-slate-700 hover:bg-slate-100 transition-colors"
                    aria-label="Menu">
                    <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-100 bg-white/95 backdrop-blur-xl px-4 py-4">
            <nav class="flex flex-col text-sm font-semibold text-slate-700">
                <a href="#hero"
                    class="mobile-nav-link px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-700">Beranda</a>
                <a href="#tentang"
                    class="mobile-nav-link px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-700">Tentang
                    Tracer</a>
                <a href="#alur"
                    class="mobile-nav-link px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-700">Alur
                    Pengisian</a>
                <a href="#faq"
                    class="mobile-nav-link px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-700">FAQ</a>
                <a href="{{ route('tracer.login') }}"
                    class="mobile-nav-link px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-700">Isi
                    Kuesioner</a>
            </nav>
            <div class="pt-3 mt-2 border-t border-slate-100">
                <a href="https://sso.unuja.ac.id"
                    class="flex items-center justify-center gap-2 px-4 py-3 text-[13px] font-bold text-white rounded-xl bg-navy-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                            d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
                    </svg>
                    Login
                </a>
            </div>
        </div>
    </header>


    <div id="hero-fixed-bg" class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <img src="{{ asset('assets/media/illustrations/unuja-jaya.png') }}"
            alt="Gedung Rektorat Universitas Nurul Jadid (UNUJA)"
            class="w-full h-full object-cover object-[center_20%]">


        <div class="absolute inset-0 bg-slate-950/[0.08]"></div>


        <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-white/90 via-white/25 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-white via-white/80 to-transparent"></div>
    </div>


    <section id="hero-section"
        class="relative z-10 overflow-hidden bg-transparent min-h-[700px] lg:min-h-[760px] flex flex-col justify-between pt-8 pb-10 sm:pt-10 sm:pb-12">
        <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div aria-hidden="true"
                class="absolute left-1/2 top-[-3rem] -translate-x-1/2 w-[92%] max-w-3xl h-[135%] bg-white/60 blur-3xl rounded-[3rem] pointer-events-none">
            </div>
            <div class="relative max-w-3xl mx-auto text-center">
                <div
                    class="reveal inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/95 backdrop-blur-md border border-white shadow-lg shadow-navy-900/10 text-xs font-semibold text-blue-900">
                    <span
                        class="px-2.5 py-0.5 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[11px] font-bold tracking-wide">RESMI</span>
                    <span class="flex items-center gap-1.5">
                        Instrumen Diktiristek &amp; BAN-PT
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </span>
                </div>

                <h1
                    class="reveal reveal-d1 mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-[1.15] text-navy-950 drop-shadow-[0_2px_12px_rgba(255,255,255,0.9)]">
                    Satu Langkah Kecil Alumni,<br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-indigo-600 to-blue-900">Lompatan
                        Besar Almamater.</span>
                </h1>

                <p
                    class="reveal reveal-d2 mt-3 text-sm sm:text-base text-slate-700 font-medium leading-relaxed max-w-xl mx-auto drop-shadow-[0_1px_8px_rgba(255,255,255,0.9)]">
                    Ceritakan perjalanan karirmu lewat Tracer Study UNUJA — cukup ±5 menit dengan NIM. Kontribusi datamu
                    menentukan mutu kampus dan akreditasi program studi.
                </p>

                <div class="reveal reveal-d2 mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('tracer.login') }}"
                        class="group inline-flex items-center justify-center gap-2.5 px-8 py-3.5 text-sm font-bold text-white bg-navy-900 hover:bg-blue-700 rounded-2xl shadow-xl shadow-navy-900/25 transition-all hover:-translate-y-0.5 w-full sm:w-auto">
                        <span>Mulai Isi Kuesioner</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="#alur"
                        class="group inline-flex items-center justify-center gap-2 px-8 py-3.5 text-sm font-bold text-navy-900 bg-white/95 hover:bg-white backdrop-blur-md rounded-2xl border border-white hover:border-blue-300 transition-all shadow-lg shadow-navy-900/10 w-full sm:w-auto">
                        <svg class="w-4 h-4 text-blue-700 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Lihat Caranya</span>
                    </a>
                </div>
            </div>
        </div>


        <div class="w-full h-12 sm:h-20 pointer-events-none"></div>


        <div class="relative w-full max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="reveal reveal-d2 max-w-xl sm:max-w-3xl mx-auto">
                <div
                    class="rounded-2xl bg-navy-950/85 backdrop-blur-xl border border-white/15 shadow-[0_18px_40px_-12px_rgba(15,39,68,0.45)] px-3 py-2.5 sm:px-8 sm:py-4">
                    <div class="grid grid-cols-3 gap-1 sm:gap-8 sm:flex sm:items-center sm:justify-center">
                        <div class="flex items-center justify-center gap-1.5 sm:gap-2.5">
                            <span
                                class="flex items-center justify-center w-6 h-6 sm:w-8 sm:h-8 rounded-lg bg-amber-500/20 border border-amber-400/30 shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-amber-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <div class="leading-tight sm:leading-normal">
                                <span class="text-[11px] sm:text-xs font-bold text-white whitespace-nowrap">±5 menit
                                </span>
                                <span
                                    class="block sm:inline text-[9px] sm:text-xs font-medium text-slate-300 whitespace-nowrap">pengisian</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-1.5 sm:gap-2.5">
                            <span
                                class="flex items-center justify-center w-6 h-6 sm:w-8 sm:h-8 rounded-lg bg-blue-500/20 border border-blue-400/30 shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
                                </svg>
                            </span>
                            <div class="leading-tight sm:leading-normal">
                                <span class="text-[11px] sm:text-xs font-bold text-white whitespace-nowrap">Tanpa akun
                                </span>
                                <span
                                    class="block sm:inline text-[9px] sm:text-xs font-medium text-slate-300 whitespace-nowrap">cukup
                                    NIM</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-1.5 sm:gap-2.5">
                            <span
                                class="flex items-center justify-center w-6 h-6 sm:w-8 sm:h-8 rounded-lg bg-emerald-500/20 border border-emerald-400/30 shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <div class="leading-tight sm:leading-normal">
                                <span class="text-[11px] sm:text-xs font-bold text-white whitespace-nowrap">Aman
                                </span>
                                <span
                                    class="block sm:inline text-[9px] sm:text-xs font-medium text-slate-300 whitespace-nowrap"><span
                                        class="hidden sm:inline">• </span>UU PDP</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="tentang" class="relative z-20">

        <div class="h-28 sm:h-36 bg-gradient-to-b from-white/0 via-white/80 to-white"></div>

        <div class="bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
                    <div class="reveal flex justify-center">
                        <svg viewBox="0 0 480 380" class="w-full max-w-xl h-auto" fill="none" role="img"
                            aria-label="Ilustrasi alumni mengisi kuesioner tracer study di ponsel">
                            <ellipse cx="230" cy="340" rx="130" ry="13" fill="#DBEAFE" />


                            <path d="M60 106h24M72 94v24" stroke="#F59E0B" stroke-width="5" stroke-linecap="round" />
                            <path d="M408 80h20M418 70v20" stroke="#818CF8" stroke-width="5"
                                stroke-linecap="round" />
                            <circle cx="96" cy="322" r="6" fill="#93C5FD" />
                            <rect x="250" y="90" width="10" height="14" rx="2" fill="#F59E0B"
                                transform="rotate(18 255 97)" />
                            <rect x="432" y="176" width="10" height="14" rx="2" fill="#818CF8"
                                transform="rotate(-15 437 183)" />
                            <circle cx="66" cy="220" r="5" fill="#6EE7B7" />


                            <rect x="76" y="274" width="26" height="16" rx="8" stroke="#92400E"
                                stroke-width="5" />
                            <rect x="48" y="286" width="80" height="56" rx="10" fill="#D97706" />
                            <rect x="48" y="286" width="80" height="22" rx="10" fill="#B45309" />
                            <rect x="81" y="302" width="14" height="10" rx="3" fill="#FDE68A" />


                            <rect x="296" y="64" width="124" height="228" rx="22" fill="#0F2744" />
                            <rect x="306" y="78" width="104" height="200" rx="12" fill="#fff" />
                            <rect x="314" y="88" width="56" height="10" rx="5" fill="#1E3A5F" />
                            <rect x="314" y="102" width="88" height="7" rx="3.5" fill="#CBD5E1" />
                            <circle cx="323" cy="123" r="9" fill="#ECFDF5" stroke="#10B981"
                                stroke-width="2" />
                            <path d="M319 123l3 3 6-6" stroke="#10B981" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <rect x="338" y="119" width="64" height="8" rx="4" fill="#E2E8F0" />
                            <circle cx="323" cy="147" r="9" fill="#ECFDF5" stroke="#10B981"
                                stroke-width="2" />
                            <path d="M319 147l3 3 6-6" stroke="#10B981" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <rect x="338" y="143" width="64" height="8" rx="4" fill="#E2E8F0" />
                            <circle cx="323" cy="171" r="9" fill="#fff" stroke="#CBD5E1"
                                stroke-width="2" />
                            <rect x="338" y="167" width="48" height="8" rx="4" fill="#F1F5F9" />
                            <rect x="314" y="190" width="88" height="10" rx="5" fill="#F1F5F9" />
                            <rect x="314" y="190" width="58" height="10" rx="5" fill="#10B981" />
                            <rect x="314" y="208" width="88" height="28" rx="14" fill="#1E40AF" />
                            <rect x="338" y="219" width="40" height="7" rx="3.5" fill="#fff"
                                opacity=".95" />
                            <rect x="338" y="284" width="40" height="5" rx="2.5" fill="#475569" />


                            <rect x="138" y="106" width="108" height="134" rx="52" fill="#1E3A5F" />

                            <rect x="146" y="210" width="92" height="116" rx="26" fill="#274B73" />
                            <polygon points="180,210 204,210 192,238" fill="#fff" />

                            <rect x="122" y="222" width="27" height="68" rx="13.5" fill="#274B73" />
                            <rect x="92" y="284" width="58" height="20" rx="10" fill="#fff"
                                stroke="#E2E8F0" stroke-width="2" />
                            <rect x="104" y="284" width="12" height="20" fill="#EF4444" />
                            <circle cx="135" cy="294" r="11" fill="#F6C89F" />

                            <rect x="234" y="230" width="72" height="26" rx="13" fill="#274B73" />
                            <circle cx="308" cy="243" r="11" fill="#F6C89F" />


                            <ellipse cx="192" cy="164" rx="33" ry="37" fill="#F6C89F" />
                            <path d="M174 196Q192 206 210 196" stroke="#EAB88A" stroke-width="3"
                                stroke-linecap="round" />
                            <circle cx="180" cy="162" r="3.6" fill="#0F2744" />
                            <circle cx="204" cy="162" r="3.6" fill="#0F2744" />
                            <ellipse cx="171" cy="174" rx="5" ry="3" fill="#F9A8A8"
                                opacity=".7" />
                            <ellipse cx="213" cy="174" rx="5" ry="3" fill="#F9A8A8"
                                opacity=".7" />
                            <path d="M182 180Q192 189 202 180" stroke="#92400E" stroke-width="3"
                                stroke-linecap="round" />


                            <polygon points="138,116 192,90 246,116 192,142" fill="#0F2744" />
                            <circle cx="192" cy="116" r="4" fill="#475569" />
                            <line x1="246" y1="116" x2="246" y2="152" stroke="#F59E0B"
                                stroke-width="4" stroke-linecap="round" />
                            <circle cx="246" cy="158" r="6" fill="#F59E0B" />


                            <circle cx="404" cy="274" r="24" fill="#ECFDF5" stroke="#A7F3D0"
                                stroke-width="2" />
                            <path d="M404 260l9 3.5v7c0 7-3.5 11.5-9 14-5.5-2.5-9-7-9-14v-7l9-3.5z" fill="#10B981" />
                            <path d="M399.5 274l3.5 3.5 6-6" stroke="#fff" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="reveal reveal-d1">
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-blue-600">Tentang</p>
                        <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-navy-900">Apa Itu <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-indigo-600 to-blue-900">TRACER
                                STUDY</span>?</h2>
                        <p class="mt-4 text-sm sm:text-[15px] text-slate-500 leading-relaxed text-justify"><strong
                                class="text-navy-900">Tracer Study UNUJA</strong> adalah portal resmi penelusuran rekam
                            jejak lulusan Universitas Nurul Jadid — mencakup <strong class="text-navy-900">seluruh
                                fakultas dan program studi</strong>. Melalui kuesioner baku berstandar <strong
                                class="text-navy-900">Diktiristek dan BAN-PT</strong>, universitas memotret perjalanan
                            alumni setelah lulus: masa tunggu kerja pertama, jenis dan bidang pekerjaan, kesesuaian ilmu
                            dengan karier, hingga kepuasan terhadap layanan kampus.</p>
                        <p class="mt-3 text-sm sm:text-[15px] text-slate-500 leading-relaxed text-justify">Bagi alumni,
                            ini cara termudah berkontribusi untuk almamater — cukup <strong class="text-navy-900">NIM
                                saja</strong>, sekitar <strong class="text-navy-900">±5 menit</strong>,
                            tanpa membuat akun. Bagi kampus, setiap jawaban menjadi <strong class="text-navy-900">bukti
                                akreditasi</strong>, bahan menyelaraskan kurikulum dengan dunia kerja, dan penguat
                            jejaring alumni. Seluruh data bersifat <strong class="text-navy-900">rahasia</strong>,
                            dilaporkan dalam bentuk <strong class="text-navy-900">agregat</strong>, dan dilindungi
                            <strong class="text-navy-900">UU PDP No. 27/2022</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="alur" class="relative z-20 py-16 sm:py-20 overflow-hidden bg-navy-900"
        style="background-color:#1e3a5f">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-blue-500/20 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute -bottom-32 -right-24 w-[28rem] h-[28rem] rounded-full bg-indigo-500/20 blur-3xl pointer-events-none">
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal max-w-2xl mx-auto text-center">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-300">Alur</p>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-white">4 Langkah, ±5 Menit</h2>
                <p class="mt-3 text-sm sm:text-base text-blue-100/80 leading-relaxed">Tanpa daftar akun — cukup NIM.
                </p>
            </div>
            <div class="relative mt-10">
                <div class="hidden lg:block absolute top-12 left-[13%] right-[13%] border-t-2 border-dashed border-white/25"
                    aria-hidden="true"></div>
                <div class="relative grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ([['01', 'Masuk', 'NIM saja, tanpa daftar akun.', 'M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3', 'from-blue-600 to-indigo-600 shadow-blue-600/30'], ['02', 'Verifikasi', 'Cek biodata & perbarui kontak.', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'from-indigo-500 to-purple-600 shadow-indigo-500/30'], ['03', 'Isi', 'Jawab riwayat karir & studi.', 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'from-amber-400 to-orange-500 shadow-orange-500/30'], ['04', 'Kirim', 'Tercatat resmi untuk akreditasi.', 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8', 'from-emerald-500 to-teal-600 shadow-emerald-500/30']] as $s)
                        <div
                            class="reveal rounded-3xl bg-white p-6 shadow-xl shadow-navy-950/20 hover:-translate-y-1.5 hover:shadow-2xl transition-all">
                            <div class="flex items-start justify-between">
                                <span
                                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br {{ $s[4] }} text-white shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $s[3] }}" />
                                    </svg>
                                </span>
                                <span
                                    class="text-4xl font-extrabold text-slate-100 leading-none select-none">{{ $s[0] }}</span>
                            </div>
                            <h3 class="mt-4 font-extrabold text-navy-900">{{ $s[1] }}</h3>
                            <p class="mt-1 text-sm text-slate-500 leading-relaxed">{{ $s[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div
                class="reveal mt-8 rounded-3xl p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-gradient-to-r from-amber-300 to-amber-400 shadow-xl shadow-navy-950/20">
                <span
                    class="hidden sm:flex items-center justify-center w-12 h-12 rounded-2xl bg-navy-900 text-amber-300 shrink-0 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <p class="flex-1 text-sm text-navy-950 leading-relaxed"><strong class="font-extrabold">Kenapa
                        penting?</strong> Datamu pengaruhi akreditasi BAN-PT/LAM, relevansi kurikulum, dan jejaring
                    alumni — tunduk pada UU Perlindungan Data Pribadi No. 27/2022.</p>
                <a href="{{ route('tracer.login') }}"
                    class="shrink-0 inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-navy-900 hover:bg-blue-700 rounded-xl shadow-lg shadow-navy-900/20 transition-all">
                    Isi Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>


    <section id="faq" class="relative z-20 py-16 sm:py-20 bg-white overflow-hidden">
        <div class="absolute top-10 -left-24 w-80 h-80 rounded-full bg-blue-100/60 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 -right-24 w-80 h-80 rounded-full bg-indigo-100/60 blur-3xl pointer-events-none">
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid lg:grid-cols-5 gap-10 lg:gap-14 items-start">
                <div class="reveal lg:col-span-2 lg:sticky lg:top-32 text-center lg:text-left">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-blue-600">FAQ</p>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-navy-900">Sering ditanyakan
                    </h2>
                    <p class="mt-3 text-sm sm:text-base text-slate-500 leading-relaxed">Jawaban cepat seputar pengisian
                        kuesioner. Masih ragu? Tim kami siap membantu.</p>
                    <div class="mt-6 rounded-3xl p-6 text-white shadow-xl shadow-navy-900/20"
                        style="background: linear-gradient(135deg, #0f2744, #1e3a5f 60%, #1e40af);">
                        <span
                            class="mx-auto lg:mx-0 flex items-center justify-center w-11 h-11 rounded-2xl bg-white/10 border border-white/15">
                            <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </span>
                        <h3 class="mt-4 font-extrabold">Butuh bantuan?</h3>
                        <p class="mt-1 text-sm text-blue-100/80">Hubungi tim Tracer Study UNUJA lewat email.</p>
                        <a href="mailto:lpip@unuja.ac.id"
                            class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-navy-900 bg-white hover:bg-amber-300 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            lpip@unuja.ac.id
                        </a>
                    </div>
                </div>
                <div class="reveal reveal-d1 lg:col-span-3 space-y-3">
                    @foreach ([
        ['Apakah alumni yang belum bekerja tetap wajib mengisi?', 'Ya, sangat wajib. Informasi mengenai alumni yang sedang dalam masa transisi, mempersiapkan diri untuk studi lanjut, atau masih mencari peluang karir sangat bernilai bagi universitas untuk memetakan kendala riil lulusan dan memperbaiki program pembekalan karir di kampus.'],
        ['Kredensial apa yang digunakan untuk masuk ke formulir?', 'Anda cukup memasukkan Nomor Induk Mahasiswa (NIM) saat kuliah sesuai data yang terdaftar pada sistem akademik. Anda tidak perlu membuat akun baru.'],
        ['Berapa lama waktu yang dibutuhkan untuk mengisi kuesioner?', 'Rata-rata waktu pengisian adalah antara 5 hingga 7 menit. Pertanyaan disajikan dalam bentuk pilihan ganda yang intuitif. Jika pengisian terputus, data yang telah diisi sebelumnya akan tetap tersimpan otomatis.'],
        ['Bagaimana jika NIM saya tidak ditemukan atau lupa?', 'Silakan periksa nomor ijazah/transkrip nilai Anda, atau hubungi Lembaga Pengembangan dan Inovasi Pembelajaran (LPIP) / Biro Kemahasiswaan UNUJA melalui kontak helpdesk di bagian bawah halaman ini dengan menyertakan nama lengkap, program studi, dan tahun kelulusan.'],
        ['Apakah data pribadi saya akan dipublikasikan secara umum?', 'Tidak. Seluruh data identitas personal, instansi bekerja, dan rentang penghasilan sepenuhnya dianonimkan dan dilindungi. Laporan yang dipublikasikan hanya berbentuk rekapitulasi statistik agregat (misal: persentase keselarasan bidang atau rata-rata waktu tunggu lulusan).'],
    ] as $f)
                        <div
                            class="faq-item bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-blue-300 hover:shadow-md transition-all">
                            <button type="button"
                                class="faq-btn w-full px-5 sm:px-6 py-4 text-left flex items-center gap-4">
                                <span
                                    class="hidden sm:flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 text-blue-700 text-xs font-extrabold shrink-0">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="flex-1 font-bold text-navy-900 text-[15px]">{{ $f[0] }}</span>
                                <span
                                    class="faq-chevron flex items-center justify-center w-8 h-8 rounded-full bg-navy-900 text-white shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 5v14M5 12h14" />
                                    </svg>
                                </span>
                            </button>
                            <div class="faq-content">
                                <div class="faq-inner">
                                    <p
                                        class="px-5 sm:px-6 sm:pl-[4.75rem] pb-5 text-sm text-slate-500 leading-relaxed">
                                        {{ $f[1] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>


    <footer class="relative z-20" style="background: linear-gradient(160deg, #0f2744, #1e3a5f 50%, #1e40af);">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-16 pb-12">
            <div class="absolute top-0 left-0 right-0 h-px"
                style="background: linear-gradient(90deg, transparent, rgba(59,130,246,0.4), transparent);"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">

                <div class="sm:col-span-2 lg:col-span-4">
                    <div class="mb-6">
                        <a href="{{ route('home') }}" class="inline-block">
                            <img src="{{ asset('assets/media/logos/tracer.png') }}"
                                alt="Tracer Study Universitas Nurul Jadid"
                                class="h-10 lg:h-[50px] w-auto object-contain hover:opacity-90 active:opacity-90 transition-opacity active:scale-[0.98]">
                        </a>
                    </div>
                    <p class="text-white/50 text-sm leading-relaxed max-w-xs">
                        Portal resmi Tracer Study Universitas Nurul Jadid untuk penelusuran rekam jejak lulusan,
                        peningkatan mutu pendidikan, dan akreditasi program studi.
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
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
                                    d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.79 1.54V6.78a4.85 4.85 0 01-1.02-.09z" />
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
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span
                                class="leading-relaxed hover:text-yellow-400 active:text-yellow-400 transition-colors cursor-default active:scale-[0.98]">JL.
                                PP
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
                                class="text-white/50 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">0888
                                30 77077</a>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-blue-400/70" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 6 2 18 2 18 9" />
                                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                                <rect x="6" y="14" width="12" height="8" />
                            </svg>
                            <span
                                class="hover:text-yellow-400 active:text-yellow-400 transition-colors cursor-default active:scale-[0.98]">Fax
                                0888 30 77077</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-blue-400/70" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:unuja@unuja.ac.id"
                                class="text-white/50 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">unuja@unuja.ac.id</a>
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
                        @foreach ([['url' => '#hero', 'label' => 'Beranda'], ['url' => '#tentang', 'label' => 'Tentang Tracer'], ['url' => '#alur', 'label' => 'Alur Pengisian'], ['url' => '#faq', 'label' => 'FAQ / Bantuan']] as $link)
                            <li><a href="{{ $link['url'] }}"
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
            </div>
        </div>

        <div class="border-t border-white/5">
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-sm text-white/30 m-0 font-medium">&copy; {{ date('Y') }} PDSI Universitas Nurul
                    Jadid. Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-4 text-sm text-white/30">
                    <a href="#faq"
                        class="text-white/30 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">Kebijakan
                        Privasi</a>
                    <span class="w-1 h-1 rounded-full bg-white/10"></span>
                    <a href="#alur"
                        class="text-white/30 no-underline hover:text-yellow-400 active:text-yellow-400 transition-colors active:scale-[0.98]">Syarat
                        &amp; Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <button id="back-top"
        class="fixed bottom-6 right-6 z-50 hidden items-center justify-center w-12 h-12 rounded-2xl bg-navy-900 text-white shadow-2xl hover:bg-blue-700 transition-all"
        aria-label="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
        </svg>
    </button>

    <script>
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');
        menuBtn.addEventListener('click', () => {
            const isHidden = mobileMenu.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden', !isHidden);
            iconClose.classList.toggle('hidden', isHidden);
        });
        document.querySelectorAll('.mobile-nav-link').forEach(a => a.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
        }));

        document.querySelectorAll('.faq-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.faq-item');
                const wasOpen = item.classList.contains('open');
                document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
                if (!wasOpen) item.classList.add('open');
            });
        });

        const io = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    io.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.12
        });
        document.querySelectorAll('.reveal').forEach(el => io.observe(el));

        window.addEventListener('load', () => {
            setTimeout(() => {
                const bar = document.getElementById('hero-progress');
                if (bar) bar.style.width = '68%';
            }, 400);
        });

        const backTop = document.getElementById('back-top');
        const navbar = document.getElementById('navbar');

        function updateScrollState() {
            const y = window.scrollY;
            if (navbar) navbar.classList.toggle('shadow-lg', y > 10);
            if (backTop) {
                if (y > 600) {
                    backTop.classList.remove('hidden');
                    backTop.classList.add('flex');
                } else {
                    backTop.classList.add('hidden');
                    backTop.classList.remove('flex');
                }
            }
        }

        window.addEventListener('scroll', updateScrollState, {
            passive: true
        });
        updateScrollState();

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        if (!window.location.hash || window.location.hash === '#hero') {
            window.scrollTo(0, 0);
        }

        backTop.addEventListener('click', () => window.scrollTo({
            top: 0,
            behavior: 'smooth'
        }));

        document.querySelectorAll('a[href="#hero"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                history.pushState(null, '', window.location.pathname);
            });
        });
    </script>
</body>

</html>
