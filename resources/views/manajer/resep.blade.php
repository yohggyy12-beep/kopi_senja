@extends('layouts.app')

@section('title', 'Manajemen Resep')
@section('page-title', 'Manajemen Resep')

@section('content')
<div class="space-y-6">

    {{-- HEADER BAR: PILIH MENU --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-muted">Tentukan takaran bahan baku per porsi untuk setiap menu penjualan.</p>
        </div>

        @if ($allMenus->isNotEmpty())
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-muted">Pilih Menu:</span>
                <select onchange="window.location.href='{{ url('/manajer/resep') }}/' + this.value"
                    class="rounded-xl border border-coffee-200 bg-white px-3.5 py-2 text-xs font-bold text-coffee-900 shadow-sm outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                    @foreach ($allMenus as $m)
                        <option value="{{ $m->id }}" {{ $menu && $menu->id === $m->id ? 'selected' : '' }}>
                            Menu: {{ $m->nama }} (Rp {{ number_format($m->harga_jual, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if (!$menu)
        <div class="rounded-2xl border border-coffee-100 bg-white p-12 text-center shadow-sm">
            <span class="text-3xl">☕</span>
            <p class="mt-3 text-sm font-bold text-ink-900">Belum ada menu terdaftar.</p>
            <p class="mt-1 text-xs text-muted">Tambahkan menu terlebih dahulu di Manajemen Menu sebelum membuat resep.</p>
            <a href="{{ route('manajer.menu.index') }}"
                class="mt-4 inline-flex rounded-xl bg-coffee-900 px-5 py-2 text-xs font-bold text-white hover:bg-coffee-800">
                Ke Manajemen Menu
            </a>
        </div>
    @else
        {{-- LAYOUT 2 KOLOM --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.6fr_1fr]">

            {{-- KOLOM KIRI: KOMPOSISI BAHAN --}}
            <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm self-start">
                <div class="flex items-center justify-between border-b border-coffee-100 px-6 py-4">
                    <div>
                        <h2 class="text-sm font-bold text-ink-900">Komposisi Bahan: {{ $menu->nama }}</h2>
                        <p class="text-xs text-muted mt-0.5">Takaran per 1 porsi saji</p>
                    </div>
                    <span class="rounded-full bg-cream-200 px-3 py-1 text-[11px] font-semibold text-coffee-900">
                        {{ $menu->detailResep->count() }} Bahan Terdaftar
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                            <tr>
                                <th class="px-6 py-3.5">Bahan Baku</th>
                                <th class="px-6 py-3.5 text-right">Takaran</th>
                                <th class="px-6 py-3.5 text-center">Satuan</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-coffee-100 text-ink-900">
                            @forelse ($menu->detailResep as $resepItem)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-ink-900">
                                        {{ $resepItem->bahanBaku->nama ?? 'Bahan Tidak Ditemukan' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-coffee-900">
                                        {{ number_format((float)$resepItem->jumlah_per_porsi, $resepItem->bahanBaku?->satuan_dasar === 'pcs' ? 0 : 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-muted font-medium">
                                        {{ $resepItem->bahanBaku->satuan_dasar ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('manajer.resep.hapus-bahan', [$menu->id, $resepItem->bahan_baku_id]) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Hapus bahan {{ addslashes($resepItem->bahanBaku?->nama) }} dari resep ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="font-semibold text-status-danger hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-muted">
                                        <p class="text-xs font-semibold text-ink-900">Menu ini belum memiliki komposisi resep.</p>
                                        <p class="mt-1 text-[11px] text-muted">Tambahkan bahan dan takaran pada panel di sebelah kanan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($menu->detailResep->isNotEmpty())
                    <div class="flex items-center justify-between border-t border-coffee-100 bg-cream-50/50 p-4">
                        <span class="text-xs text-status-success font-semibold">✓ Resep lengkap dan siap dipakai di kasir</span>

                        <form action="{{ route('manajer.resep.destroy', $menu->id) }}" method="POST"
                            onsubmit="return confirm('Kosongkan seluruh komposisi resep menu {{ addslashes($menu->nama) }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-status-danger hover:underline font-semibold">
                                Kosongkan Resep
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- KOLOM KANAN: FORM TAMBAH BAHAN KE RESEP --}}
            <div class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm self-start sticky top-24">
                <div class="border-b border-coffee-100 pb-3">
                    <h3 class="text-sm font-bold text-ink-900">Tambah Bahan ke Resep</h3>
                    <p class="mt-0.5 text-xs text-muted">Pilih bahan dan masukkan takaran per satu porsi.</p>
                </div>

                @if ($errors->has('bahan_baku_id'))
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3.5 text-xs font-medium text-status-danger">
                        {{ $errors->first('bahan_baku_id') }}
                    </div>
                @endif

                <form action="{{ route('manajer.resep.store', $menu->id) }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="bahan_baku_id" class="mb-1 block text-xs font-medium text-ink-900">Pilih Bahan Baku</label>
                        <select id="bahan_baku_id" name="bahan_baku_id" required
                            class="w-full rounded-xl border border-coffee-200 bg-white px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                            <option value="">-- Pilih bahan baku --</option>
                            @foreach ($semuaBahan as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->nama }} ({{ $b->satuan_dasar }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="jumlah_per_porsi" class="mb-1 block text-xs font-medium text-ink-900">Jumlah Takaran</label>
                        <input id="jumlah_per_porsi" name="jumlah_per_porsi" type="number" step="any" min="0.001" required
                            placeholder="Contoh: 18 atau 120"
                            class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                        <p class="mt-1 text-[11px] text-muted">Masukkan sesuai satuan dasar bahan (g, ml, atau pcs).</p>
                    </div>

                    <div class="pt-3 border-t border-coffee-100">
                        <button type="submit"
                            class="w-full rounded-xl bg-coffee-900 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
                            ＋ Tambah ke Resep
                        </button>
                    </div>
                </form>
            </div>

        </div>
    @endif

</div>
@endsection
