@extends('layouts.app')

@section('title', 'Manajemen Pembelian Stok')
@section('page-title', 'Manajemen Pembelian Stok')

@section('content')
<div class="space-y-6">

    {{-- TOP ACTION BAR --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Catat pembelian persediaan untuk menambah stok gudang secara otomatis.</p>
        </div>
        <span class="inline-flex rounded-full bg-green-50 border border-green-200 px-3.5 py-1 text-xs font-semibold text-status-success">
            ✓ Stok Gudang Diperbarui Otomatis
        </span>
    </div>

    {{-- LAYOUT 2 KOLOM --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.6fr_1fr]">

        {{-- KOLOM KIRI: RIWAYAT PEMBELIAN STOK --}}
        <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm self-start">
            <div class="flex items-center justify-between border-b border-coffee-100 px-6 py-4">
                <h2 class="text-sm font-bold text-ink-900">Riwayat Pembelian Stok</h2>
                <span class="text-xs text-muted font-medium">Dicatat oleh Manajer</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5">Bahan Baku</th>
                            <th class="px-5 py-3.5 text-right">Jumlah</th>
                            <th class="px-5 py-3.5 text-right">Estimasi Biaya</th>
                            <th class="px-5 py-3.5">Pencatat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-100 text-ink-900">
                        @forelse ($riwayat as $item)
                            <tr class="transition hover:bg-cream-50/50">
                                <td class="px-5 py-4 text-muted whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($item->tanggal_pembelian)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-4 font-semibold text-ink-900">
                                    {{ $item->bahan_baku->nama ?? 'Bahan Dihapus' }}
                                </td>
                                <td class="px-5 py-4 text-right font-bold text-status-success">
                                    +{{ number_format((float)$item->jumlah, $item->bahan_baku?->satuan_dasar === 'pcs' ? 0 : 0, ',', '.') }} {{ $item->bahan_baku?->satuan_dasar }}
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-coffee-900">
                                    Rp {{ number_format(((float)$item->jumlah * (float)($item->bahan_baku?->harga_per_satuan_dasar ?? 0)), 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 font-medium text-ink-900">
                                    {{ $item->dicatat_oleh_pengguna->nama ?? 'Manajer' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-muted">
                                    <p class="text-xs font-semibold text-ink-900">Belum ada riwayat pembelian stok.</p>
                                    <p class="mt-1 text-[11px] text-muted">Catat pembelian baru pada form di sisi kanan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($riwayat->hasPages())
                <div class="border-t border-coffee-100 p-4">
                    {{ $riwayat->links() }}
                </div>
            @endif
        </div>

        {{-- KOLOM KANAN: FORM CATAT PEMBELIAN BARU --}}
        <div class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm self-start sticky top-24">
            <div class="border-b border-coffee-100 pb-3">
                <h3 class="text-sm font-bold text-ink-900">Catat Pembelian Baru</h3>
                <p class="mt-0.5 text-xs text-muted">Masukkan bahan masuk dan jumlah untuk menambah persediaan.</p>
            </div>

            <form action="{{ route('manajer.pembelian-stok.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                {{-- PILIH BAHAN BAKU --}}
                <div>
                    <label for="bahan_baku_id" class="mb-1 block text-xs font-medium text-ink-900">Bahan Baku</label>
                    <select id="bahan_baku_id" name="bahan_baku_id" required
                        class="w-full rounded-xl border border-coffee-200 bg-white px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                        <option value="">-- Pilih bahan baku --</option>
                        @foreach ($semuaBahan as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->nama }} ({{ $b->satuan_dasar }}) • Stok: {{ number_format($b->jumlah_stok, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- TANGGAL PEMBELIAN & JUMLAH MASUK --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tanggal_pembelian" class="mb-1 block text-xs font-medium text-ink-900">Tanggal</label>
                        <input id="tanggal_pembelian" name="tanggal_pembelian" type="date" required
                            value="{{ date('Y-m-d') }}"
                            class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                    </div>

                    <div>
                        <label for="jumlah" class="mb-1 block text-xs font-medium text-ink-900">Jumlah Masuk</label>
                        <input id="jumlah" name="jumlah" type="number" step="any" min="0.001" required
                            placeholder="Contoh: 5000"
                            class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                    </div>
                </div>

                {{-- DICATAT OLEH (READ ONLY) --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink-900">Dicatat Oleh</label>
                    <input type="text" value="{{ auth()->user()->nama }}" disabled
                        class="w-full rounded-xl border border-coffee-100 bg-cream-50 px-3.5 py-2 text-xs text-muted font-semibold cursor-not-allowed">
                </div>

                <div class="pt-3 border-t border-coffee-100">
                    <button type="submit"
                        class="w-full rounded-xl bg-coffee-900 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
                        Simpan & Tambah Stok
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
