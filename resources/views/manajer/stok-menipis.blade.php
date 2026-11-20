@extends('layouts.app')

@section('title', 'Peringatan Stok Menipis')
@section('page-title', 'Peringatan Stok Menipis')

@section('content')
<div class="space-y-6">

    {{-- TOP BAR --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Daftar bahan baku yang berada di bawah atau sama dengan batas stok minimum.</p>
        </div>

        <span class="rounded-full bg-red-100 px-4 py-1.5 text-xs font-bold text-status-danger">
            {{ $data->count() }} Bahan Kritis
        </span>
    </div>

    {{-- TABEL BAHAN KRITIS --}}
    <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">Bahan Baku</th>
                        <th class="px-6 py-3.5 text-right">Stok Saat Ini</th>
                        <th class="px-6 py-3.5 text-right">Batas Minimum</th>
                        <th class="px-6 py-3.5 text-center">Kondisi</th>
                        <th class="px-6 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-ink-900">
                    @forelse ($data as $bahan)
                        @php
                            $stok = (float) $bahan->jumlah_stok;
                            $minimum = (float) $bahan->stok_minimum;
                            $isHabis = $stok <= 0;
                        @endphp
                        <tr class="transition hover:bg-cream-50/50">
                            <td class="px-6 py-4 font-bold text-ink-900">
                                {{ $bahan->nama }}
                                @if($bahan->kode_pemasok)
                                    <span class="block text-[10px] font-normal text-muted">Kode: {{ $bahan->kode_pemasok }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-extrabold {{ $isHabis ? 'text-status-danger' : 'text-status-warning' }}">
                                {{ number_format($stok, 0, ',', '.') }} {{ $bahan->satuan_dasar }}
                            </td>
                            <td class="px-6 py-4 text-right text-muted font-medium">
                                {{ number_format($minimum, 0, ',', '.') }} {{ $bahan->satuan_dasar }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($isHabis)
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-status-danger">
                                        Habis
                                    </span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-status-warning">
                                        Menipis
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('manajer.pembelian-stok.index') }}"
                                    class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-coffee-800">
                                    Restok Sekarang
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="text-3xl">🎉</div>
                                <p class="mt-2 text-sm font-bold text-ink-900">Seluruh stok bahan baku aman!</p>
                                <p class="mt-1 text-xs text-muted">Tidak ada bahan baku yang berada di bawah batas minimum.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
