@extends('layouts.app')

@section('title', 'HPP dan Margin Kotor')
@section('page-title', 'HPP dan Margin Kotor')

@section('content')
<div class="space-y-6">

    {{-- TOP BAR --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Analisis biaya bahan baku (HPP) per porsi menu dan proyeksi margin keuntungan kotor.</p>
        </div>
        <span class="rounded-xl border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-coffee-900 shadow-sm">
            Acuan: Harga Supplier Terakhir
        </span>
    </div>

    {{-- TABEL HPP & MARGIN --}}
    <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">Nama Menu</th>
                        <th class="px-6 py-3.5 text-right">Harga Jual</th>
                        <th class="px-6 py-3.5 text-right">HPP Resep</th>
                        <th class="px-6 py-3.5 text-right">Margin Kotor (Rp)</th>
                        <th class="px-6 py-3.5 text-center">Persentase Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-ink-900">
                    @forelse ($daftarHpp as $item)
                        <tr class="transition hover:bg-cream-50/50">
                            <td class="px-6 py-4 font-bold text-ink-900">
                                {{ $item->nama }}
                                <span class="block text-[10px] font-normal text-muted">{{ ucfirst($item->kategori) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-ink-900">
                                Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-coffee-800">
                                @if ($item->resep_lengkap && $item->hpp > 0)
                                    Rp {{ number_format($item->hpp, 0, ',', '.') }}
                                @else
                                    <span class="text-muted font-normal text-[11px]">Belum dihitung</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-status-success">
                                @if ($item->resep_lengkap && $item->hpp > 0)
                                    Rp {{ number_format($item->margin_kotor, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->resep_lengkap && $item->hpp > 0)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-[11px] font-bold text-status-success">
                                        {{ number_format($item->persentase, 1, ',', '.') }}%
                                    </span>
                                @else
                                    <span class="rounded-full bg-cream-200 px-3 py-1 text-[11px] font-medium text-muted">
                                        Perlu Resep
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-muted">
                                <p class="text-xs font-semibold text-ink-900">Belum ada menu terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
