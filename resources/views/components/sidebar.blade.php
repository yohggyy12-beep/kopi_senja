@php
    $pengguna = auth()->user();

    $isManajer = $pengguna?->isManager() ?? false;
    $isKasir = $pengguna?->isKasir() ?? false;

    $menuAktif = 'flex items-center gap-3 rounded-lg bg-coffee-900 px-4 py-3 text-sm font-semibold text-white';

    $menuNormal = 'flex items-center gap-3 rounded-lg px-4 py-3 text-sm text-ink-900 transition hover:bg-cream-100';
@endphp

<aside id="appSidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-coffee-100 bg-white transition-transform duration-200 lg:translate-x-0">

    {{-- BRAND DAN LOGO SEMENTARA --}}
    <div class="flex min-h-20 items-center gap-3 border-b border-coffee-100 px-6">

        {{-- LOGO SEMENTARA: ganti dengan logo buatanmu nanti --}}
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-coffee-900 text-sm font-bold text-white">
            KS
        </div>

        <div>
            <h2 class="text-sm font-bold text-coffee-900">
                Kedai Kopi Senja
            </h2>

            <p class="mt-1 text-xs text-muted">
                {{ $isManajer ? 'Manajer' : 'Kasir' }}
            </p>
        </div>

    </div>


    {{-- NAVIGASI --}}
    <nav class="flex-1 space-y-1 overflow-y-auto p-4">

        <p class="mb-3 px-3 pt-2 text-[10px] font-bold uppercase tracking-widest text-muted">
            Menu Utama
        </p>


        {{-- NAVIGASI MANAJER --}}
        @if ($isManajer)
            <a href="{{ url('/manajer/dashboard') }}"
                class="{{ request()->is('manajer/dashboard') ? $menuAktif : $menuNormal }}">
                <span>⌂</span>
                Dashboard
            </a>

            <a href="{{ url('/manajer/bahan-baku') }}"
                class="{{ request()->is('manajer/bahan-baku*') ? $menuAktif : $menuNormal }}">
                <span>▦</span>
                Bahan Baku
            </a>

            <a href="{{ url('/manajer/menu') }}"
                class="{{ request()->is('manajer/menu*') ? $menuAktif : $menuNormal }}">
                <span>☕</span>
                Manajemen Menu
            </a>

            <a href="{{ url('/manajer/resep') }}"
                class="{{ request()->is('manajer/resep*') ? $menuAktif : $menuNormal }}">
                <span>▤</span>
                Manajemen Resep
            </a>

            <a href="{{ url('/manajer/pembelian-stok') }}"
                class="{{ request()->is('manajer/pembelian-stok*') ? $menuAktif : $menuNormal }}">
                <span>＋</span>
                Pembelian Stok
            </a>

            <a href="{{ url('/manajer/stok-menipis') }}"
                class="{{ request()->is('manajer/stok-menipis*') ? $menuAktif : $menuNormal }}">
                <span>!</span>
                Stok Menipis
            </a>

            <a href="{{ url('/manajer/harga-bahan') }}"
                class="{{ request()->is('manajer/harga-bahan*') ? $menuAktif : $menuNormal }}">
                <span>↻</span>
                Harga Supplier
            </a>

            <a href="{{ url('/manajer/hpp-margin') }}"
                class="{{ request()->is('manajer/hpp-margin*') ? $menuAktif : $menuNormal }}">
                <span>◇</span>
                HPP dan Margin
            </a>

            <a href="{{ url('/manajer/laporan-penjualan') }}"
                class="{{ request()->is('manajer/laporan-penjualan*') ? $menuAktif : $menuNormal }}">
                <span>▥</span>
                Laporan Penjualan
            </a>

            <a href="{{ url('/manajer/laporan-persediaan') }}"
                class="{{ request()->is('manajer/laporan-persediaan*') ? $menuAktif : $menuNormal }}">
                <span>▤</span>
                Laporan Persediaan
            </a>


            {{-- NAVIGASI KASIR --}}
        @elseif ($isKasir)
            <a href="{{ url('/kasir/dashboard') }}"
                class="{{ request()->is('kasir/dashboard') ? $menuAktif : $menuNormal }}">
                <span>⌂</span>
                Dashboard
            </a>

            <a href="{{ url('/kasir/pesanan/create') }}"
                class="{{ request()->is('kasir/pesanan/create') ? $menuAktif : $menuNormal }}">
                <span>＋</span>
                Buat Pesanan
            </a>

            <a href="{{ url('/kasir/pesanan') }}"
                class="{{ request()->is('kasir/pesanan') || request()->is('kasir/pesanan/*') ? $menuAktif : $menuNormal }}">
                <span>▤</span>
                Daftar Pesanan
            </a>

            <a href="{{ url('/kasir/reset-password-pelanggan') }}"
                class="{{ request()->is('kasir/reset-password-pelanggan*') ? $menuAktif : $menuNormal }}">
                <span>♙</span>
                Reset Password Pelanggan
            </a>
        @endif


        {{-- PROFIL --}}
        <div class="my-4 border-t border-coffee-100"></div>

        <a href="{{ url('/change-password') }}"
            class="{{ request()->is('change-password') ? $menuAktif : $menuNormal }}">
            <span>♙</span>
            Profil dan Password
        </a>

    </nav>


    {{-- LOGOUT --}}
    <div class="border-t border-coffee-100 p-4">

        <form action="{{ url('/logout') }}" method="POST">

            @csrf

            <button type="submit"
                class="flex w-full items-center gap-3 rounded-lg border border-coffee-100 px-4 py-3 text-left text-sm text-status-danger transition hover:bg-red-50">
                <span>↪</span>
                Logout
            </button>

        </form>

    </div>

</aside>
