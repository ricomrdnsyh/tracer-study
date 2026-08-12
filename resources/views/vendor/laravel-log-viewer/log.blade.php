@php use Illuminate\Support\Facades\Crypt; @endphp
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Tracer - Log Viewer</title>
    <meta name="description" content="Tracer Study - Universitas Nurul Jadid">
    <meta name="author" content="Universitas Nurul Jadid">
    <meta name="publisher" content="Pusat Data & Sistem Informasi Universitas Nurul Jadid">
    <meta name="language" content="Indonesian">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp, noydir, nocache, notranslate">
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet, notranslate">
    <meta name="bingbot" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="slurp" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="duckduckbot" content="noindex, nofollow, noarchive, nosnippet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            safelist: [
                'bg-danger/15', 'bg-warning/15', 'bg-info/15', 'bg-success/15', 'bg-primary/15', 'bg-secondary/15',
                'bg-dark/15',
                'text-danger', 'text-warning', 'text-info', 'text-success', 'text-primary', 'text-secondary',
                'text-dark',
                'border-danger/20', 'border-warning/20', 'border-info/20', 'border-success/20', 'border-primary/20',
                'border-secondary/20', 'border-dark/20',
                'bg-error/15', 'text-error', 'border-error/20'
            ],
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        primary: '#2563eb',
                        surface: '#ffffff',
                        border: '#e2e8f0',
                        danger: '#ef4444',
                        warning: '#f59e0b',
                        info: '#3b82f6',
                        success: '#10b981',
                        secondary: '#64748b',
                        dark: '#1e293b',
                        error: '#ef4444',
                        debug: '#64748b',
                    },
                    borderRadius: {
                        xl: '12px',
                        '2xl': '16px'
                    },
                    transitionProperty: {
                        all: 'all'
                    }
                }
            }
        }
    </script>
    <link rel="icon" href="{{ asset('assets/media/logos/unuja.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        table.dataTable.no-footer {
            border-bottom: none !important;
        }

        .dataTables_wrapper .dataTables_length select {
            padding-right: 2rem !important;
            border-radius: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem !important;
            padding: 0.5rem 1rem !important;
        }

        .dark .dataTables_wrapper .dataTables_length select,
        .dark .dataTables_wrapper .dataTables_filter input {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
    </style>
</head>

<body
    class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-all font-sans antialiased selection:bg-primary/30">
    <div class="h-screen w-full flex flex-col md:flex-row relative overflow-hidden">

        <div
            class="absolute -top-40 -left-40 w-96 h-96 bg-primary/20 rounded-full blur-[100px] pointer-events-none z-0">
        </div>
        <div
            class="absolute -bottom-40 -right-40 w-96 h-96 bg-info/20 rounded-full blur-[100px] pointer-events-none z-0">
        </div>

        <aside
            class="w-full md:w-72 lg:w-80 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-800 flex flex-col z-30 shadow-[4px_0_24px_rgba(0,0,0,0.02)] relative shrink-0 transition-all duration-300"
            id="sidebar">
            <div
                class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between lg:h-[88px] shrink-0">
                <div class="flex items-center gap-3 text-primary font-extrabold text-xl tracking-tight">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-blue-600 text-white flex items-center justify-center shadow-lg shadow-primary/30 shrink-0">
                        <i class="fas fa-terminal text-sm"></i>
                    </div>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary to-blue-600">Log
                        Viewer</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="toggleTheme()"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary hover:text-white dark:hover:bg-primary transition-all shadow-sm">
                        <i class="fas fa-moon hidden dark:inline-block"></i>
                        <i class="fas fa-sun dark:hidden"></i>
                    </button>
                    <button onclick="toggleMobileMenu()"
                        class="w-10 h-10 flex md:hidden items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary hover:text-white transition-all shadow-sm">
                        <i class="fas fa-bars" id="mobile-menu-icon"></i>
                    </button>
                </div>
            </div>

            <div id="sidebar-content"
                class="hidden md:flex flex-1 overflow-y-auto p-4 flex-col space-y-2 bg-white/80 dark:bg-slate-900/80 md:bg-transparent absolute md:relative top-[88px] md:top-0 left-0 w-full h-[calc(100vh-88px)] md:h-auto z-20">
                @foreach ($folders as $folder)
                    <div class="group relative">
                        <div
                            class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors border border-transparent dark:hover:border-slate-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600 dark:text-yellow-500">
                                <i class="fas fa-folder"></i>
                            </div>
                            <span class="text-sm font-semibold truncate text-slate-700 dark:text-slate-200"
                                title="{{ basename($folder) }}">
                                {{ basename($folder) }}
                            </span>
                        </div>
                        @if (count($folder['subfolders']) > 0)
                            <div
                                class="ml-4 hidden group-hover:block absolute left-full top-0 z-50 min-w-[200px] bg-white dark:bg-slate-800 shadow-xl border border-slate-200 dark:border-slate-700 rounded-xl p-2 transform transition-all">
                                @foreach ($folder['subfolders'] as $sub)
                                    <a href="?l={{ Crypt::encryptString($sub) }}"
                                        class="flex items-center gap-3 p-2.5 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-slate-700 dark:text-slate-200 transition-colors">
                                        <i class="fas fa-folder text-yellow-500"></i>
                                        {{ basename($sub) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <div>
                    <div class="space-y-1.5">
                        @foreach ($files as $file)
                            <a href="?l={{ Crypt::encryptString($file) }}" @class([
                                'flex items-center gap-3 p-3 rounded-xl text-sm font-medium truncate transition-all duration-300 relative overflow-hidden group',
                                'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' =>
                                    $current_file == $file,
                                'hover:bg-slate-100 dark:hover:bg-slate-800 hover:translate-x-1 text-slate-600 dark:text-slate-400 border border-transparent dark:hover:border-slate-700' =>
                                    $current_file != $file,
                            ])
                                title="{{ $file }}">
                                @if ($current_file == $file)
                                    <div class="absolute inset-0 bg-gradient-to-r from-primary to-blue-500 opacity-100">
                                    </div>
                                @endif
                                <div class="relative z-10 flex items-center gap-3 w-full">
                                    <i
                                        class="fas fa-file-alt {{ $current_file == $file ? 'text-blue-100' : 'text-slate-400 group-hover:text-primary' }}"></i>
                                    <span class="relative z-10 truncate">{{ basename($file) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-hidden z-10 relative bg-white/40 dark:bg-slate-900/40">
            <div
                class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl sticky top-0 z-20 lg:h-[88px] shrink-0">
                <div class="min-w-0">
                    @if ($current_file)
                        <div class="flex items-center gap-3 mb-1">
                            <span
                                class="px-2.5 py-1 rounded-md bg-blue-100 dark:bg-blue-900/30 text-primary text-xs font-bold uppercase tracking-wider">Active
                                Log</span>
                            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white truncate">
                                {{ basename($current_file) }}</h1>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fas fa-list-ul"></i>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ count($logs) }}</span>
                            entries found in this file
                        </p>
                    @else
                        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Select a log file</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Choose a file from the sidebar to
                            view its contents.</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    @if ($current_file)
                        <button onclick="downloadLog()"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:border-primary hover:text-primary dark:hover:border-primary transition-all shadow-sm hover:shadow">
                            <i class="fas fa-download"></i>
                            <span class="hidden sm:inline">Download</span>
                        </button>
                        <button onclick="cleanLog()"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:border-warning hover:text-warning dark:hover:border-warning transition-all shadow-sm hover:shadow">
                            <i class="fas fa-broom"></i>
                            <span class="hidden sm:inline">Clean</span>
                        </button>
                        <button onclick="deleteLog()"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold bg-white dark:bg-slate-800 text-error border-2 border-error/20 dark:border-error/30 rounded-xl hover:bg-error hover:text-white transition-all shadow-sm hover:shadow">
                            <i class="fas fa-trash-alt"></i>
                            <span class="hidden sm:inline">Delete</span>
                        </button>
                        @if (count($files) > 1)
                            <button onclick="deleteAllLogs()"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold bg-error text-white border-2 border-error rounded-xl hover:bg-red-600 hover:border-red-600 transition-all shadow-sm hover:shadow shadow-error/20">
                                <i class="fas fa-dumpster"></i>
                                <span class="hidden lg:inline">Delete All</span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            @if ($logs === null)
                <div
                    class="p-8 m-6 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 flex items-start gap-4">
                    <div
                        class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center flex-shrink-0 text-error">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-800 dark:text-red-400 mb-1">File Too Large</h3>
                        <p class="text-red-600 dark:text-red-300 text-sm">
                            The log file is over 50MB and cannot be displayed in the browser. Please
                            <a href="?dl={{ Crypt::encryptString($current_file) }}"
                                class="font-bold underline hover:text-red-800 dark:hover:text-red-200 transition-colors">download
                                it</a> to view its contents.
                        </p>
                    </div>
                </div>
            @else
                <div class="flex-1 overflow-auto p-6 lg:p-8">
                    <div
                        class="bg-white dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-700 p-2 overflow-x-auto">
                        <table id="logTable" class="w-full display responsive nowrap !border-collapse">
                            <thead>
                                <tr class="text-left bg-slate-50 dark:bg-slate-900/50">
                                    <th
                                        class="px-5 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider rounded-tl-xl w-[120px]">
                                        Level</th>
                                    <th
                                        class="px-5 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-[180px]">
                                        Context</th>
                                    <th
                                        class="px-5 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-[180px]">
                                        Date & Time</th>
                                    <th
                                        class="px-5 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider rounded-tr-xl">
                                        Content</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @if ($logs)
                                    @foreach ($logs as $key => $log)
                                        <tr data-stack="stack{{ $key }}"
                                            class="hover:bg-blue-50/50 dark:hover:bg-slate-800 transition-colors cursor-pointer group"
                                            onclick="toggleStack('stack{{ $key }}')">
                                            <td class="px-5 py-4 align-top">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-{{ $log['level_class'] }}/15 text-{{ $log['level_class'] }} whitespace-nowrap shadow-sm border border-{{ $log['level_class'] }}/20 uppercase tracking-wider">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                    {{ $log['level'] }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300 truncate align-top"
                                                title="{{ $log['context'] }}">
                                                {{ $log['context'] }}
                                            </td>
                                            <td
                                                class="px-5 py-4 text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap align-top">
                                                <div class="flex items-center gap-2">
                                                    <i class="far fa-clock text-slate-400"></i>
                                                    {{ \Carbon\Carbon::parse($log['date'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <div class="flex flex-col gap-3">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <pre class="font-mono text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap break-words flex-1">{{ $log['text'] }}</pre>
                                                        @if ($log['stack'])
                                                            <button
                                                                class="text-slate-400 group-hover:text-primary group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-900 flex items-center justify-center border border-slate-200 dark:border-slate-700 transition-all">
                                                                <i class="fas fa-chevron-down transition-transform duration-300"
                                                                    id="icon-stack{{ $key }}"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    @if ($log['stack'])
                                                        <div id="stack{{ $key }}"
                                                            class="hidden mt-2 p-4 bg-slate-900 text-slate-300 rounded-xl shadow-inner border border-slate-800 overflow-x-auto w-full relative"
                                                            onclick="event.stopPropagation()">
                                                            <div
                                                                class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-blue-500 opacity-50">
                                                            </div>
                                                            <pre class="font-mono text-xs whitespace-pre-wrap break-words">{{ trim($log['stack']) }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div id="loading"
                class="fixed inset-0 bg-white/60 dark:bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden transition-opacity z-50">
                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-2xl flex flex-col items-center gap-4 border border-slate-200 dark:border-slate-700">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-4 border-slate-100 dark:border-slate-700 border-t-primary">
                    </div>
                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Processing...</span>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    <script>
        $(document).ready(function() {
            if ($('#logTable').length) {
                $('#logTable').DataTable({
                    responsive: true,
                    ordering: false,
                    stateSave: true,
                    stateDuration: -1,
                    pageLength: 25,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    columnDefs: [{
                            targets: 0,
                            className: 'align-top'
                        },
                        {
                            targets: [1, 2],
                            responsivePriority: 2
                        },
                        {
                            targets: 3,
                            responsivePriority: 1
                        }
                    ],
                    language: {
                        emptyTable: '<div class="flex flex-col items-center justify-center p-8 text-center opacity-70"><div class="w-16 h-16 mb-4 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl text-slate-300 dark:text-slate-600"><i class="fas fa-check-circle"></i></div><h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">Log is Empty</h3><p class="text-slate-500 dark:text-slate-400 max-w-sm text-sm">Tidak ada data di log ini.</p></div>',
                        search: '',
                        searchPlaceholder: 'Search logs...',
                        lengthMenu: 'Show _MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        paginate: {
                            first: '<i class="fas fa-angle-double-left"></i>',
                            last: '<i class="fas fa-angle-double-right"></i>',
                            next: '<i class="fas fa-angle-right"></i>',
                            previous: '<i class="fas fa-angle-left"></i>'
                        }
                    },
                    drawCallback: function() {
                        $('.dataTables_paginate > .pagination').addClass('flex items-center gap-1');
                        $('.paginate_button').addClass(
                            'px-3 py-1.5 rounded-lg font-medium text-sm transition-colors');
                    }
                });
            }
        });

        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        (function initTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.toggle('dark', savedTheme === 'dark');
        })();

        function toggleMobileMenu() {
            const content = document.getElementById('sidebar-content');
            const icon = document.getElementById('mobile-menu-icon');

            content.classList.toggle('hidden');
            content.classList.toggle('flex');

            if (content.classList.contains('hidden')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        }

        function toggleStack(id) {
            const stack = document.getElementById(id);
            if (!stack) return;

            stack.classList.toggle('hidden');
            const icon = document.getElementById('icon-' + id);
            if (icon) {
                icon.style.transform = stack.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        }

        const fileActions = {
            showLoading: () => document.getElementById('loading').classList.remove('hidden'),
            hideLoading: () => document.getElementById('loading').classList.add('hidden'),

            downloadLog: () => {
                fileActions.showLoading();
                window.location.href =
                    `?dl={{ Crypt::encryptString($current_file) }}{{ $current_folder ? '&f=' . Crypt::encryptString($current_folder) : '' }}`;
                setTimeout(fileActions.hideLoading, 2000);
            },

            cleanLog: () => {
                if (confirm('Are you sure you want to clean this log file? All contents will be erased.')) {
                    fileActions.showLoading();
                    window.location.href =
                        `?clean={{ Crypt::encryptString($current_file) }}{{ $current_folder ? '&f=' . Crypt::encryptString($current_folder) : '' }}`;
                }
            },

            deleteLog: () => {
                if (confirm('Are you sure you want to delete this log file? This action cannot be undone.')) {
                    fileActions.showLoading();
                    window.location.href =
                        `?del={{ Crypt::encryptString($current_file) }}{{ $current_folder ? '&f=' . Crypt::encryptString($current_folder) : '' }}`;
                }
            },

            deleteAllLogs: () => {
                if (confirm(
                        'WARNING: Are you sure you want to delete ALL log files? This action cannot be undone.')) {
                    fileActions.showLoading();
                    window.location.href =
                        `?delall=true{{ $current_folder ? '&f=' . Crypt::encryptString($current_folder) : '' }}`;
                }
            }
        };

        window.downloadLog = fileActions.downloadLog;
        window.cleanLog = fileActions.cleanLog;
        window.deleteLog = fileActions.deleteLog;
        window.deleteAllLogs = fileActions.deleteAllLogs;
    </script>
</body>

</html>
