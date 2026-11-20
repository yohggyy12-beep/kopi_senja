@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">

    {{-- TOP BAR: TOGGLE LAPORAN PENJUALAN & PERSEDIAAN --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Ringkasan transaksi pendapatan kasir dan performa per menu.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('manajer.laporan.penjualan') }}"
                class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-bold text-white shadow-sm">
                Penjualan
            </a>
            <a href="{{ route('manajer.laporan.persediaan') }}"
                class="rounded-xl border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-ink-900 hover:bg-cream-100">
                Persediaan
            </a>
        </div>
    </div>

    {{-- FILTER PERIODE --}}
    <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('manajer.laporan.penjualan') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2 text-xs text-ink-900 font-medium">
                <span>Periode:</span>
                <input type="date" name="mulai" value="{{ $mulai }}"
                    class="rounded-xl border border-coffee-200 px-3 py-1.5 text-xs text-ink-900 outline-none transition focus:border-coffee-700">
                <span>—</span>
                <input type="date" name="selesai" value="{{ $selesai }}"
                    class="rounded-xl border border-coffee-200 px-3 py-1.5 text-xs text-ink-900 outline-none transition focus:border-coffee-700">
            </div>

            <button type="submit"
                class="rounded-xl bg-coffee-900 px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
                Terapkan Filter
            </button>
        </form>
    </div>

    {{-- KARTU METRIK RINGKASAN --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-muted">Total Omzet</p>
            <p class="mt-3 text-2xl font-extrabold text-coffee-900">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-muted">Total Transaksi</p>
            <p class="mt-3 text-2xl font-extrabold text-coffee-900">{{ number_format($totalTransaksi, 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-muted">Rata-rata Struk</p>
            <p class="mt-3 text-2xl font-extrabold text-coffee-900">Rp {{ number_format($rataRataStruk, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- TABEL PENJUALAN PER MENU --}}
    <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">Menu</th>
                        <th class="px-6 py-3.5 text-center">Terjual</th>
                        <th class="px-6 py-3.5 text-right">Total Penjualan</th>
                        <th class="px-6 py-3.5 text-right">Laba Kotor (Estimasi)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-ink-900">
                    @forelse ($penjualanMenu as $item)
                        @php
                            // Estimasi laba kotor 60%
                            $estimasiLaba = $item->total_pendapatan * 0.62;
                        @endphp
                        <tr class="transition hover:bg-cream-50/50">
                            <td class="px-6 py-4 font-bold text-ink-900">
                                {{ $item->nama }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-coffee-900">
                                {{ number_format($item->total_terjual, 0, ',', '.') }} porsi
                            </td>
                            <td class="px-6 py-4 text-right font-extrabold text-coffee-900">
                                Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-status-success">
                                Rp {{ number_format($estimasiLaba, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-muted">
                                <p class="text-xs font-semibold text-ink-900">Tidak ada transaksi penjualan pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
