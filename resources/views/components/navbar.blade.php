@php
    $pengguna = auth()->user();

    $namaPengguna = $pengguna->nama
        ?? $pengguna->nama_pengguna
        ?? 'Pengguna';

    $bagianNama = preg_split('/\s+/', trim($namaPengguna));

    $inisial = collect($bagianNama)
        ->take(2)
        ->map(fn ($kata) => mb_substr($kata, 0, 1))
        ->implode('');
@endphp

<header class="sticky top-0 z-20 flex min-h-[72px] items-center justify-between gap-4 border-b border-coffee-100 bg-white px-5 py-4 lg:px-8">

    {{-- JUDUL HALAMAN --}}
    <div class="flex items-center gap-4">

        <button
            type="button"
            data-sidebar-toggle
            aria-expanded="false"
            aria-label="Buka navigasi"
            class="rounded-lg border border-coffee-100 px-3 py-2 text-coffee-900 hover:bg-cream-100 lg:hidden"
        >
            ☰
        </button>

        <h1 class="text-xl font-bold text-coffee-900">
            @yield('page-title', 'Dashboard')
        </h1>

    </div>


    {{-- INFORMASI PENGGUNA --}}
    <div class="flex items-center gap-3">

        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-coffee-900 text-sm font-bold text-white">
            {{ strtoupper($inisial) }}
        </div>

        <div class="hidden sm:block">

            <p class="text-sm font-semibold text-ink-900">
                {{ $namaPengguna }}
            </p>

            <p class="mt-0.5 text-xs text-muted">
                {{ $pengguna->isManager() ? 'Manajer Operasional' : 'Kasir' }}
            </p>

        </div>

    </div>

</header>
