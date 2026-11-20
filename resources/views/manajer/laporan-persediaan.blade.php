@extends('layouts.app')

@section('title', 'Laporan Persediaan')
@section('page-title', 'Laporan Persediaan')

@section('content')
<div class="space-y-6">

    {{-- TOP BAR: TOGGLE LAPORAN --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Pemantauan arus masuk dan pemakaian bahan baku serta valuasi aset stok.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('manajer.laporan.penjualan') }}"
                class="rounded-xl border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-ink-900 hover:bg-cream-100">
                Penjualan
            </a>
            <a href="{{ route('manajer.laporan.persediaan') }}"
                class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-bold text-white shadow-sm">
                Persediaan
            </a>
        </div>
    </div>

    {{-- FILTER PERIODE --}}
    <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('manajer.laporan.persediaan') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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

    {{-- TABEL ARUS PERSEDIAAN --}}
    <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">Bahan Baku</th>
                        <th class="px-6 py-3.5 text-right">Masuk</th>
                        <th class="px-6 py-3.5 text-right">Terpakai</th>
                        <th class="px-6 py-3.5 text-right">Sisa Akhir</th>
                        <th class="px-6 py-3.5 text-right">Nilai Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-ink-900">
                    @forelse ($daftarPersediaan as $item)
                        <tr class="transition hover:bg-cream-50/50">
                            <td class="px-6 py-4 font-bold text-ink-900">
                                {{ $item->nama }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-status-success">
                                @if ($item->masuk > 0)
                                    +{{ number_format($item->masuk, 0, ',', '.') }} {{ $item->satuan }}
                                @else
                                    <span class="text-muted font-normal">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-status-danger">
                                @if ($item->terpakai > 0)
                                    -{{ number_format($item->terpakai, 0, ',', '.') }} {{ $item->satuan }}
                                @else
                                    <span class="text-muted font-normal">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-extrabold text-ink-900">
                                {{ number_format($item->sisa_akhir, 0, ',', '.') }} {{ $item->satuan }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-coffee-900">
                                Rp {{ number_format($item->nilai_stok, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-muted">
                                <p class="text-xs font-semibold text-ink-900">Belum ada data bahan baku.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
