@extends('layouts.app')

@section('title', 'Manajemen Bahan Baku')
@section('page-title', 'Manajemen Bahan Baku')

@section('content')
<div class="space-y-6">

    {{-- TOP ACTION BAR --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Kelola daftar master bahan baku, satuan dasar, dan batas stok minimum.</p>
        </div>
        <a href="{{ route('manajer.bahan-baku.index') }}#formBahan"
            class="inline-flex items-center gap-2 rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
            <span>＋</span>
            Tambah Bahan Baku
        </a>
    </div>

    {{-- LAYOUT 2 KOLOM --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.6fr_1fr]">

        {{-- KOLOM KIRI: TABEL BAHAN BAKU --}}
        <div class="space-y-4">

            {{-- SEARCH & FILTER BAR --}}
            <div class="rounded-2xl border border-coffee-100 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('manajer.bahan-baku.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <input type="text" name="q" value="{{ $q ?? '' }}"
                            placeholder="Cari nama bahan baku..."
                            class="w-full rounded-xl border border-coffee-200 bg-cream-50/50 px-4 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:bg-white focus:ring-1 focus:ring-coffee-700">
                    </div>

                    <div class="w-full sm:w-44">
                        <select name="status" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-coffee-200 bg-white px-3 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                            <option value="semua" {{ ($status ?? '') === 'semua' ? 'selected' : '' }}>Semua Kondisi</option>
                            <option value="menipis" {{ ($status ?? '') === 'menipis' ? 'selected' : '' }}>Menipis / Kritis</option>
                        </select>
                    </div>
                </form>
            </div>

            {{-- TABEL DATA --}}
            <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                            <tr>
                                <th class="px-5 py-3.5">Nama Bahan</th>
                                <th class="px-5 py-3.5 text-center">Satuan</th>
                                <th class="px-5 py-3.5 text-right">Stok</th>
                                <th class="px-5 py-3.5 text-right">Minimum</th>
                                <th class="px-5 py-3.5 text-center">Kondisi</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-coffee-100 text-ink-900">
                            @forelse ($bahanBakuList as $bahan)
                                @php
                                    $stok = (float) $bahan->jumlah_stok;
                                    $minimum = (float) $bahan->stok_minimum;
                                @endphp
                                <tr class="transition hover:bg-cream-50/50 {{ isset($editItem) && $editItem->id === $bahan->id ? 'bg-cream-100/70' : '' }}">
                                    <td class="px-5 py-4 font-semibold text-ink-900">
                                        {{ $bahan->nama }}
                                        @if($bahan->kode_pemasok)
                                            <span class="block text-[10px] font-normal text-muted">Kode: {{ $bahan->kode_pemasok }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center font-medium text-muted">
                                        {{ $bahan->satuan_dasar }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-bold text-ink-900">
                                        {{ number_format($stok, $bahan->satuan_dasar === 'pcs' ? 0 : 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4 text-right text-muted font-medium">
                                        {{ number_format($minimum, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($stok <= 0)
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-status-danger">
                                                Habis
                                            </span>
                                        @elseif ($stok <= $minimum)
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-status-warning">
                                                Menipis
                                            </span>
                                        @else
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-[11px] font-semibold text-status-success">
                                                Aman
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('manajer.bahan-baku.index', ['edit' => $bahan->id]) }}#formBahan"
                                            class="rounded-lg border border-coffee-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-coffee-900 hover:bg-cream-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('manajer.bahan-baku.destroy', $bahan->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus bahan baku {{ addslashes($bahan->nama) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-red-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-status-danger hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-muted">
                                        <p class="text-xs font-semibold text-ink-900">Belum ada data bahan baku.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($bahanBakuList->hasPages())
                    <div class="border-t border-coffee-100 p-4">
                        {{ $bahanBakuList->links() }}
                    </div>
                @endif
            </div>

        </div>

        {{-- KOLOM KANAN: FORM BAHAN BAKU --}}
        <div id="formBahan" class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm self-start sticky top-24">
            <div class="border-b border-coffee-100 pb-3">
                <h3 class="text-sm font-bold text-ink-900">
                    {{ isset($editItem) ? 'Edit Bahan Baku: ' . $editItem->nama : 'Form Bahan Baku' }}
                </h3>
                <p class="mt-0.5 text-xs text-muted">
                    {{ isset($editItem) ? 'Perbarui informasi satuan atau stok minimum.' : 'Masukkan data bahan baru ke database.' }}
                </p>
            </div>

            <form action="{{ isset($editItem) ? route('manajer.bahan-baku.update', $editItem->id) : route('manajer.bahan-baku.store') }}"
                method="POST" class="mt-5 space-y-4">
                @csrf
                @if (isset($editItem))
                    @method('PUT')
                @endif

                {{-- NAMA BAHAN --}}
                <div>
                    <label for="nama" class="mb-1 block text-xs font-medium text-ink-900">Nama Bahan</label>
                    <input id="nama" name="nama" type="text" required
                        value="{{ old('nama', $editItem->nama ?? '') }}"
                        placeholder="Contoh: Sirup Gula Aren"
                        class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                </div>

                {{-- SATUAN DASAR --}}
                <div>
                    <label for="satuan_dasar" class="mb-1 block text-xs font-medium text-ink-900">Satuan</label>
                    <select id="satuan_dasar" name="satuan_dasar" required
                        class="w-full rounded-xl border border-coffee-200 bg-white px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                        <option value="g" {{ old('satuan_dasar', $editItem->satuan_dasar ?? '') === 'g' ? 'selected' : '' }}>Gram (g)</option>
                        <option value="ml" {{ old('satuan_dasar', $editItem->satuan_dasar ?? '') === 'ml' ? 'selected' : '' }}>Mililiter (ml)</option>
                        <option value="pcs" {{ old('satuan_dasar', $editItem->satuan_dasar ?? '') === 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                    </select>
                </div>

                {{-- STOK AWAL & STOK MINIMUM --}}
                <div class="grid grid-cols-2 gap-3">
                    @if (!isset($editItem))
                        <div>
                            <label for="stok_awal" class="mb-1 block text-xs font-medium text-ink-900">Stok Awal</label>
                            <input id="stok_awal" name="stok_awal" type="number" step="any" min="0" value="0" disabled
                                class="w-full rounded-xl border border-coffee-100 bg-cream-50 px-3.5 py-2 text-xs text-muted cursor-not-allowed"
                                title="Stok awal dimulai 0. Gunakan Pembelian Stok untuk menambah.">
                        </div>
                    @else
                        <div>
                            <label class="mb-1 block text-xs font-medium text-ink-900">Stok Saat Ini</label>
                            <input type="text" value="{{ number_format($editItem->jumlah_stok, 0, ',', '.') }} {{ $editItem->satuan_dasar }}" disabled
                                class="w-full rounded-xl border border-coffee-100 bg-cream-50 px-3.5 py-2 text-xs text-muted font-bold cursor-not-allowed">
                        </div>
                    @endif

                    <div>
                        <label for="stok_minimum" class="mb-1 block text-xs font-medium text-ink-900">Minimum</label>
                        <input id="stok_minimum" name="stok_minimum" type="number" step="any" min="0" required
                            value="{{ old('stok_minimum', $editItem->stok_minimum ?? 100) }}"
                            class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                    </div>
                </div>

                {{-- KODE PEMASOK (OPTIONAL) --}}
                <div>
                    <label for="kode_pemasok" class="mb-1 block text-xs font-medium text-ink-900">Kode Pemasok (Opsional)</label>
                    <input id="kode_pemasok" name="kode_pemasok" type="text"
                        value="{{ old('kode_pemasok', $editItem->kode_pemasok ?? '') }}"
                        placeholder="Contoh: SUP-BJK-01"
                        class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-coffee-100">
                    @if (isset($editItem))
                        <a href="{{ route('manajer.bahan-baku.index') }}"
                            class="rounded-xl border border-coffee-200 px-4 py-2.5 text-xs font-semibold text-ink-900 hover:bg-cream-100">
                            Batal
                        </a>
                    @else
                        <button type="reset"
                            class="rounded-xl border border-coffee-200 px-4 py-2.5 text-xs font-semibold text-ink-900 hover:bg-cream-100">
                            Batal
                        </button>
                    @endif

                    <button type="submit"
                        class="rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
                        {{ isset($editItem) ? 'Perbarui Bahan' : 'Simpan Bahan' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
