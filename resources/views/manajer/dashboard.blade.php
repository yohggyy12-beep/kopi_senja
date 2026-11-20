@extends('layouts.app')

@section('title', 'Dashboard Manajer')

@section('page-title', 'Dashboard Manajer')


@section('content')

    {{-- ==================================================
        BAGIAN 1: INFORMASI DAN AKSES CEPAT
    ================================================== --}}

    <section class="mb-6">

        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

            <div>

                <p class="text-sm text-muted">

                    Ringkasan operasional Kedai Kopi Senja hari ini,
                    {{ now()->locale('id')->translatedFormat('d F Y') }}.

                </p>

            </div>


            {{-- TOMBOL AKSES CEPAT --}}
            <div class="flex flex-wrap gap-2">

                <a href="{{ url('/manajer/bahan-baku') }}"
                    class="rounded-lg bg-coffee-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-coffee-800">
                    Bahan Baku
                </a>

                <a href="{{ url('/manajer/menu') }}"
                    class="rounded-lg border border-coffee-200 bg-white px-4 py-2.5 text-xs font-semibold text-coffee-900 transition hover:bg-cream-100">
                    Menu
                </a>

                <a href="{{ url('/manajer/resep') }}"
                    class="rounded-lg border border-coffee-200 bg-white px-4 py-2.5 text-xs font-semibold text-coffee-900 transition hover:bg-cream-100">
                    Resep
                </a>

                <a href="{{ url('/manajer/laporan-penjualan') }}"
                    class="rounded-lg border border-coffee-200 bg-white px-4 py-2.5 text-xs font-semibold text-coffee-900 transition hover:bg-cream-100">
                    Laporan
                </a>

            </div>

        </div>

    </section>



    {{-- ==================================================
        BAGIAN 2: KARTU STATISTIK
    ================================================== --}}

    @php
        $kartuStatistik = [
            [
                'judul' => 'Total Penjualan',
                'nilai' => 'Rp ' . number_format($totalPenjualan ?? 0, 0, ',', '.'),
                'keterangan' => 'Akumulasi transaksi aktif',
                'warna' => 'text-coffee-900',
            ],

            [
                'judul' => 'Jumlah Pesanan',
                'nilai' => number_format($jumlahPesanan, 0, ',', '.'),
                'keterangan' => 'Seluruh pesanan tercatat',
                'warna' => 'text-coffee-900',
            ],

            [
                'judul' => 'Total Menu',
                'nilai' => number_format($jumlahMenu, 0, ',', '.'),
                'keterangan' => 'Menu terdaftar',
                'warna' => 'text-coffee-900',
            ],

            [
                'judul' => 'Total Bahan Baku',
                'nilai' => number_format($jumlahBahanBaku, 0, ',', '.'),
                'keterangan' => 'Jenis bahan terdaftar',
                'warna' => 'text-coffee-900',
            ],

            [
                'judul' => 'Stok Menipis',
                'nilai' => number_format($jumlahStokMenipis, 0, ',', '.'),
                'keterangan' => 'Perlu diperiksa',
                'warna' => 'text-red-700',
            ],
        ];
    @endphp


    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">

        @foreach ($kartuStatistik as $kartu)
            <div class="rounded-xl border border-coffee-100 bg-white p-4 shadow-sm">

                <p class="text-xs font-medium text-muted">
                    {{ $kartu['judul'] }}
                </p>


                <p class="mt-3 text-2xl font-bold {{ $kartu['warna'] }}">

                    @if ($kartu['nilai'] === null)
                        —
                    @else
                        {{ $kartu['nilai'] }}
                    @endif

                </p>


                <p class="mt-2 text-xs text-muted">

                    {{ $kartu['keterangan'] }}

                </p>

            </div>
        @endforeach

    </section>



    {{-- ==================================================
        BAGIAN 3: MENU TERLARIS DAN AKTIVITAS TERBARU
    ================================================== --}}

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[0.9fr_1.5fr]">


        {{-- PANEL MENU TERLARIS --}}
        <div class="overflow-hidden rounded-xl border border-coffee-100 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-coffee-100 px-5 py-4">

                <h2 class="text-sm font-bold text-ink-900">
                    Menu Terlaris
                </h2>

                <span class="text-xs text-muted">
                    Pekan Ini
                </span>

            </div>


            <div class="p-5">

                @forelse (($menuTerlaris ?? []) as $menu)
                    <div class="flex items-center justify-between gap-3 border-b border-coffee-100 py-3 last:border-0">

                        <div class="flex min-w-0 items-center gap-3">

                            {{-- FOTO MENU SEMENTARA --}}
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cream-200">
                                ☕
                            </div>

                            <div class="min-w-0">

                                <p class="truncate text-sm font-semibold text-ink-900">
                                    {{ $menu['nama'] }}
                                </p>

                                <p class="mt-1 text-xs text-muted">
                                    {{ number_format($menu['jumlah_terjual'], 0, ',', '.') }}
                                    porsi
                                </p>

                            </div>

                        </div>


                        <p class="shrink-0 text-sm font-semibold text-coffee-700">

                            Rp {{ number_format($menu['harga_jual'], 0, ',', '.') }}

                        </p>

                    </div>

                @empty

                    <div class="py-10 text-center">

                        <div class="mb-3 text-3xl">
                            ☕
                        </div>

                        <p class="text-sm font-semibold text-ink-900">
                            Belum ada data menu terlaris
                        </p>

                        <p class="mt-1 text-xs text-muted">
                            Data akan muncul setelah transaksi tersedia.
                        </p>

                    </div>
                @endforelse

            </div>

        </div>



        {{-- PANEL AKTIVITAS TERBARU --}}
        <div class="overflow-hidden rounded-xl border border-coffee-100 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-coffee-100 px-5 py-4">

                <h2 class="text-sm font-bold text-ink-900">
                    Aktivitas Terbaru (Pembelian Stok & Pesanan)
                </h2>

                <span class="text-xs text-muted">
                    Pemantauan Saya
                </span>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full min-w-[600px] text-left">

                    <thead class="bg-cream-200">

                        <tr class="text-[10px] font-bold uppercase tracking-wider text-muted">

                            <th class="px-4 py-3">
                                ID
                            </th>

                            <th class="px-4 py-3">
                                Keterangan
                            </th>

                            <th class="px-4 py-3">
                                Nominal
                            </th>

                            <th class="px-4 py-3">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse (($aktivitasTerbaru ?? []) as $aktivitas)
                            <tr class="border-t border-coffee-100 text-xs text-ink-900">

                                <td class="px-4 py-4 font-medium">

                                    {{ $aktivitas['nomor'] }}

                                </td>


                                <td class="px-4 py-4">

                                    {{ $aktivitas['keterangan'] }}

                                </td>


                                <td class="px-4 py-4 font-semibold">

                                    Rp {{ number_format($aktivitas['nominal'], 0, ',', '.') }}

                                </td>


                                <td class="px-4 py-4">

                                    <span
                                        class="rounded-full px-3 py-1 text-[10px] font-semibold
                                        {{ $aktivitas['status'] === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">

                                        {{ ucfirst($aktivitas['status']) }}

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4" class="px-4 py-12 text-center">

                                    <p class="text-sm font-semibold text-ink-900">
                                        Belum ada aktivitas terbaru.
                                    </p>

                                    <p class="mt-1 text-xs text-muted">
                                        Riwayat transaksi dan pembelian akan muncul di sini.
                                    </p>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </section>

@endsection
