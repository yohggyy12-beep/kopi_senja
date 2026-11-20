@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('page-title', 'Dashboard Kasir')

@section('content')
    {{-- BARIS ATAS: KARTU METRIK & TOMBOL BUAT PESANAN --}}
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm text-muted">Ringkasan transaksi dan antrean pesanan aktif kasir saat ini.</p>
        </div>
        <div>
            <a href="{{ url('/kasir/pesanan/create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-coffee-800">
                <span>＋</span>
                Buat Pesanan Baru
            </a>
        </div>
    </div>

    {{-- KARTU STATISTIK SHIFT --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- KARTU 1 --}}
        <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-muted">Pesanan Shift Ini</p>
            <p class="mt-3 text-3xl font-bold text-coffee-900">{{ $totalPesananShift }}</p>
            <p class="mt-2 text-xs font-medium text-muted">
                <span class="text-status-success font-semibold">{{ $pesananSelesaiShift }} selesai</span> • {{ $pesananAktifShift }} aktif
            </p>
        </div>

        {{-- KARTU 2 --}}
        <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-muted">Pendapatan Shift</p>
            <p class="mt-3 text-3xl font-bold text-coffee-900">Rp {{ number_format($pendapatanShift, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-muted">Tunai & QRIS</p>
        </div>

        {{-- KARTU 3 --}}
        <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-muted">Pesanan Diproses</p>
            <p class="mt-3 text-3xl font-bold text-coffee-900">{{ $pesananDiproses }}</p>
            <p class="mt-2 text-xs text-muted">Menunggu penyajian</p>
        </div>

        {{-- KARTU 4 --}}
        <div class="rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-muted">Menu Tersedia</p>
            <p class="mt-3 text-3xl font-bold text-coffee-900">{{ $menuTersedia }} / {{ $totalMenu }}</p>
            <p class="mt-2 text-xs {{ $menuHabis > 0 ? 'text-status-danger font-semibold' : 'text-muted' }}">
                {{ $menuHabis > 0 ? $menuHabis . ' menu bahan habis' : 'Semua menu siap' }}
            </p>
        </div>
    </div>

    {{-- ANTREAN PESANAN AKTIF --}}
    <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-coffee-100 px-6 py-4">
            <h2 class="text-sm font-bold text-ink-900">Antrean Pesanan Aktif</h2>
            <a href="{{ url('/kasir/pesanan') }}" class="text-xs font-semibold text-coffee-700 hover:text-coffee-900">
                Lihat Semua Pesanan
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">ID Pesanan</th>
                        <th class="px-6 py-3.5">Ringkasan Item</th>
                        <th class="px-6 py-3.5">Total</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-ink-900">
                    @forelse ($antreanPesanan as $pesanan)
                        <tr class="transition hover:bg-cream-50/50">
                            <td class="px-6 py-4 font-semibold text-coffee-900">
                                <a href="{{ url('/kasir/pesanan/' . $pesanan->id) }}" class="underline decoration-coffee-200 underline-offset-2 hover:text-coffee-700">
                                    #{{ $pesanan->nomor_pesanan }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-ink-900">
                                    {{ $pesanan->detailPesanan->map(fn($d) => "{$d->jumlah}x {$d->nama_menu_saat_pemesanan}")->take(2)->implode(', ') }}
                                    {{ $pesanan->detailPesanan->count() > 2 ? ' +' . ($pesanan->detailPesanan->count() - 2) . ' lainnya' : '' }}
                                    @if ($pesanan->nomor_meja)
                                        <span class="text-muted">(M-{{ sprintf('%02d', $pesanan->nomor_meja) }})</span>
                                    @else
                                        <span class="text-muted">(Takeaway)</span>
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4 font-semibold">
                                Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($pesanan->status === 'baru')
                                    <span class="rounded-full bg-cream-200 px-3 py-1 text-[11px] font-semibold text-coffee-900">
                                        Baru
                                    </span>
                                @elseif ($pesanan->status === 'diproses')
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-900">
                                        Diproses
                                    </span>
                                @else
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-[11px] font-semibold text-green-900">
                                        {{ ucfirst($pesanan->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($pesanan->status === 'baru')
                                    <form action="{{ url('/kasir/pesanan/' . $pesanan->id . '/status') }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="diproses">
                                        <button type="submit"
                                            class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-coffee-800">
                                            Proses Pesanan
                                        </button>
                                    </form>
                                @elseif ($pesanan->status === 'diproses')
                                    <form action="{{ url('/kasir/pesanan/' . $pesanan->id . '/status') }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="selesai">
                                        <button type="submit"
                                            class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-coffee-800">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <p class="text-sm font-semibold text-ink-900">Tidak ada antrean pesanan aktif saat ini.</p>
                                <p class="mt-1 text-xs text-muted">Pesanan baru yang dibuat akan langsung muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
