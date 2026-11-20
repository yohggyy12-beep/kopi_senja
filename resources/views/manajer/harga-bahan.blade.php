@extends('layouts.app')

@section('title', 'Harga Supplier')
@section('page-title', 'Harga Supplier')

@section('content')
<div class="space-y-6">

    {{-- HEADER & TOMBOL SINKRON --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Sinkronisasi harga acuan bahan baku dari API server Supplier eksternal.</p>
        </div>

        <form action="{{ route('manajer.harga-bahan.perbarui') }}" method="POST">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
                <span>↻</span>
                Sinkronkan Harga Supplier
            </button>
        </form>
    </div>

    {{-- BANNER STATUS SINKRONISASI --}}
    @php
        $sync = session('sync_result');
    @endphp

    @if ($sync)
        <div class="rounded-2xl border {{ $sync['berhasil'] ? 'border-green-200 bg-green-50 text-green-900' : 'border-amber-200 bg-amber-50 text-amber-900' }} p-4 text-xs flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="text-base">{{ $sync['berhasil'] ? '✓' : '⚠️' }}</span>
                <p class="font-medium">
                    {{ $sync['pesan'] }} (Sumber: <span class="font-bold uppercase">{{ $sync['sumber'] }}</span>)
                </p>
            </div>
            @if(isset($sync['jumlah_diperbarui']))
                <span class="font-bold shrink-0">
                    {{ $sync['jumlah_diperbarui'] }} Bahan Terbarui
                </span>
            @endif
        </div>
    @elseif ($terakhirSinkron)
        <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-xs text-green-900 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="text-base">✓</span>
                <p class="font-medium">
                    Sinkronisasi API Supplier aktif. Terakhir diperbarui pada {{ \Illuminate\Support\Carbon::parse($terakhirSinkron)->translatedFormat('d M Y, H:i') }} WIB (autentikasi server-side aktif).
                </p>
            </div>
            <span class="font-bold shrink-0">
                Data Acuan Valid
            </span>
        </div>
    @endif

    {{-- TABEL HARGA SUPPLIER --}}
    <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">Bahan Baku</th>
                        <th class="px-6 py-3.5">Kode Pemasok</th>
                        <th class="px-6 py-3.5 text-right">Harga per Satuan</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Terakhir Sinkron</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-ink-900">
                    @forelse ($daftarBahan as $bahan)
                        <tr class="transition hover:bg-cream-50/50">
                            <td class="px-6 py-4 font-bold text-ink-900">
                                {{ $bahan->nama }}
                            </td>
                            <td class="px-6 py-4 text-muted font-medium">
                                {{ $bahan->kode_pemasok ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-extrabold text-coffee-900">
                                @if ($bahan->harga_per_satuan_dasar > 0)
                                    Rp {{ number_format($bahan->harga_per_satuan_dasar, 0, ',', '.') }} / {{ $bahan->satuan_dasar }}
                                @else
                                    <span class="text-muted font-normal">Belum ada harga</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($bahan->harga_per_satuan_dasar > 0)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-[11px] font-semibold text-status-success">
                                        Stabil
                                    </span>
                                @else
                                    <span class="rounded-full bg-cream-200 px-3 py-1 text-[11px] font-semibold text-muted">
                                        Menunggu API
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-muted whitespace-nowrap">
                                @if ($bahan->tanggal_pembaruan_harga_pemasok)
                                    {{ \Illuminate\Support\Carbon::parse($bahan->tanggal_pembaruan_harga_pemasok)->translatedFormat('d M Y') }}
                                @else
                                    -
                                @endif
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

        @if ($daftarBahan->hasPages())
            <div class="border-t border-coffee-100 p-4">
                {{ $daftarBahan->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
