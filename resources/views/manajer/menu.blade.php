@extends('layouts.app')

@section('title', 'Manajemen Menu')
@section('page-title', 'Manajemen Menu')

@section('content')
<div class="space-y-6">

    {{-- TOP ACTION BAR --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted">Kelola nama menu, kategori, harga jual, dan status katalog penjualan.</p>
        </div>
        <a href="{{ route('manajer.menu.index') }}#formMenu"
            class="inline-flex items-center gap-2 rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
            <span>＋</span>
            Tambah Menu
        </a>
    </div>

    {{-- LAYOUT 2 KOLOM --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.6fr_1fr]">

        {{-- KOLOM KIRI: TABEL MENU --}}
        <div class="space-y-4">

            {{-- SEARCH & FILTER BAR --}}
            <div class="rounded-2xl border border-coffee-100 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('manajer.menu.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <input type="text" name="q" value="{{ $q ?? '' }}"
                            placeholder="Cari nama menu..."
                            class="w-full rounded-xl border border-coffee-200 bg-cream-50/50 px-4 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:bg-white focus:ring-1 focus:ring-coffee-700">
                    </div>

                    <div class="w-full sm:w-44">
                        <select name="kategori" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-coffee-200 bg-white px-3 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                            <option value="">Semua Kategori</option>
                            <option value="Minuman" {{ ($kategori ?? '') === 'Minuman' ? 'selected' : '' }}>Minuman</option>
                            <option value="Makanan" {{ ($kategori ?? '') === 'Makanan' ? 'selected' : '' }}>Makanan</option>
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
                                <th class="px-5 py-3.5">Nama Menu</th>
                                <th class="px-5 py-3.5">Kategori</th>
                                <th class="px-5 py-3.5">Harga Jual</th>
                                <th class="px-5 py-3.5 text-center">Status Katalog</th>
                                <th class="px-5 py-3.5 text-center">Ketersediaan</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-coffee-100 text-ink-900">
                            @forelse ($menus as $menu)
                                <tr class="transition hover:bg-cream-50/50 {{ isset($editItem) && $editItem->id === $menu->id ? 'bg-cream-100/70' : '' }}">
                                    <td class="px-5 py-4 font-semibold text-ink-900">
                                        {{ $menu->nama }}
                                    </td>
                                    <td class="px-5 py-4 text-muted font-medium">
                                        {{ ucfirst($menu->kategori) }}
                                    </td>
                                    <td class="px-5 py-4 font-bold text-coffee-900">
                                        Rp {{ number_format($menu->harga_jual, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($menu->status_aktif)
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-[11px] font-semibold text-status-success">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-cream-200 px-3 py-1 text-[11px] font-semibold text-muted">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($menu->resep_lengkap)
                                            <span class="rounded-full bg-green-50 border border-green-200 px-3 py-1 text-[11px] font-semibold text-status-success">
                                                Tersedia
                                            </span>
                                        @else
                                            <span class="rounded-full bg-red-50 border border-red-200 px-3 py-1 text-[11px] font-semibold text-status-danger">
                                                Belum Ada Resep
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('manajer.resep.show', $menu->id) }}"
                                            class="rounded-lg border border-coffee-200 bg-cream-50 px-2.5 py-1 text-[11px] font-semibold text-coffee-900 hover:bg-cream-100"
                                            title="Atur resep bahan">
                                            Resep
                                        </a>

                                        <a href="{{ route('manajer.menu.index', ['edit' => $menu->id]) }}#formMenu"
                                            class="rounded-lg border border-coffee-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-coffee-900 hover:bg-cream-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('manajer.menu.destroy', $menu->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu {{ addslashes($menu->nama) }}?');">
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
                                        <p class="text-xs font-semibold text-ink-900">Belum ada data menu.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($menus->hasPages())
                    <div class="border-t border-coffee-100 p-4">
                        {{ $menus->links() }}
                    </div>
                @endif
            </div>

        </div>

        {{-- KOLOM KANAN: FORM MENU --}}
        <div id="formMenu" class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm self-start sticky top-24">
            <div class="border-b border-coffee-100 pb-3">
                <h3 class="text-sm font-bold text-ink-900">
                    {{ isset($editItem) ? 'Edit Menu: ' . $editItem->nama : 'Form Menu' }}
                </h3>
                <p class="mt-0.5 text-xs text-muted">
                    {{ isset($editItem) ? 'Perbarui informasi menu atau ubah status jual.' : 'Tambahkan menu baru ke dalam katalog kedai.' }}
                </p>
            </div>

            <form action="{{ isset($editItem) ? route('manajer.menu.update', $editItem->id) : route('manajer.menu.store') }}"
                method="POST" class="mt-5 space-y-4">
                @csrf
                @if (isset($editItem))
                    @method('PUT')
                @endif

                {{-- NAMA MENU --}}
                <div>
                    <label for="nama" class="mb-1 block text-xs font-medium text-ink-900">Nama Menu</label>
                    <input id="nama" name="nama" type="text" required
                        value="{{ old('nama', $editItem->nama ?? '') }}"
                        placeholder="Contoh: Hot Cappuccino"
                        class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                </div>

                {{-- KATEGORI & HARGA JUAL --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="kategori" class="mb-1 block text-xs font-medium text-ink-900">Kategori</label>
                        <select id="kategori" name="kategori" required
                            class="w-full rounded-xl border border-coffee-200 bg-white px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                            @foreach ($kategoriOptions as $opt)
                                <option value="{{ $opt }}" {{ old('kategori', $editItem->kategori ?? '') === $opt ? 'selected' : '' }}>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="harga_jual" class="mb-1 block text-xs font-medium text-ink-900">Harga Jual (Rp)</label>
                        <input id="harga_jual" name="harga_jual" type="number" step="100" min="0" required
                            value="{{ old('harga_jual', isset($editItem) ? (int)$editItem->harga_jual : '') }}"
                            placeholder="24000"
                            class="w-full rounded-xl border border-coffee-200 px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                    </div>
                </div>

                {{-- STATUS KATALOG --}}
                @if (isset($editItem))
                    <div>
                        <label for="status_aktif" class="mb-1 block text-xs font-medium text-ink-900">Status Katalog</label>
                        <select id="status_aktif" name="status_aktif" required
                            class="w-full rounded-xl border border-coffee-200 bg-white px-3.5 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                            <option value="1" {{ old('status_aktif', $editItem->status_aktif) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status_aktif', $editItem->status_aktif) == 0 ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                @endif

                {{-- TOMBOL AKSI --}}
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-coffee-100">
                    @if (isset($editItem))
                        <a href="{{ route('manajer.menu.index') }}"
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
                        {{ isset($editItem) ? 'Perbarui Menu' : 'Simpan Menu' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
