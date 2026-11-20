@extends('layouts.app')

@section('title', 'Daftar Pesanan')
@section('page-title', 'Daftar Pesanan')

@section('content')
<div class="space-y-6">

    {{-- FILTER BAR & SEARCH --}}
    <div class="rounded-2xl border border-coffee-100 bg-white p-4 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('kasir.pesanan.index') }}" class="flex-1 max-w-md">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                    placeholder="Cari nomor pesanan atau meja..."
                    class="w-full rounded-xl border border-coffee-200 bg-cream-50/50 px-4 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:bg-white focus:ring-1 focus:ring-coffee-700">
            </div>
        </form>

        {{-- STATUS PILLS --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            @php
                $statusTabs = [
                    'semua' => 'Semua',
                    'baru' => 'Baru',
                    'diproses' => 'Diproses',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                ];
            @endphp

            @foreach ($statusTabs as $key => $label)
                <a href="{{ route('kasir.pesanan.index', array_merge(request()->query(), ['status' => $key])) }}"
                    class="rounded-full px-4 py-1.5 text-xs font-semibold transition {{ ($statusAktif ?? 'semua') === $key ? 'bg-coffee-900 text-white' : 'border border-coffee-200 bg-white text-ink-900 hover:bg-cream-100' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- TABEL DAFTAR PESANAN --}}
    <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">ID Pesanan</th>
                        <th class="px-6 py-3.5">Meja / Metode</th>
                        <th class="px-6 py-3.5">Total</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Langkah Berikutnya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-ink-900">
                    @forelse ($pesanan as $item)
                        <tr class="transition hover:bg-cream-50/50">
                            <td class="px-6 py-4 font-semibold text-coffee-900">
                                <a href="{{ route('kasir.pesanan.show', $item->id) }}"
                                    class="underline decoration-coffee-200 underline-offset-2 hover:text-coffee-700">
                                    #{{ $item->nomor_pesanan }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-muted">
                                @if ($item->nomor_meja)
                                    <span class="font-medium text-ink-900">Meja M-{{ sprintf('%02d', $item->nomor_meja) }}</span>
                                @else
                                    <span class="font-medium text-ink-900">Takeaway</span>
                                @endif
                                • {{ strtoupper($item->metode_pembayaran) }}
                            </td>
                            <td class="px-6 py-4 font-bold text-coffee-900">
                                Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($item->status === 'baru')
                                    <span class="rounded-full bg-cream-200 px-3 py-1 text-[11px] font-semibold text-coffee-900">
                                        Baru
                                    </span>
                                @elseif ($item->status === 'diproses')
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-900">
                                        Diproses
                                    </span>
                                @elseif ($item->status === 'selesai')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-[11px] font-semibold text-status-success">
                                        Selesai
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-status-danger">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($item->status === 'baru')
                                    <form action="{{ route('kasir.pesanan.status', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="diproses">
                                        <button type="submit"
                                            class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-coffee-800">
                                            Proses Pesanan
                                        </button>
                                    </form>
                                @elseif ($item->status === 'diproses')
                                    <form action="{{ route('kasir.pesanan.status', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="selesai">
                                        <button type="submit"
                                            class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-coffee-800">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                @elseif ($item->status === 'selesai')
                                    <a href="{{ route('kasir.pesanan.struk', $item->id) }}"
                                        class="rounded-xl border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-coffee-900 transition hover:bg-cream-100">
                                        Lihat Struk
                                    </a>
                                @else
                                    <span class="text-xs text-muted font-medium">Status Final</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <p class="text-sm font-semibold text-ink-900">Tidak ada pesanan ditemukan.</p>
                                <p class="mt-1 text-xs text-muted">Pesanan dengan filter yang dipilih belum tersedia.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pesanan->hasPages())
            <div class="border-t border-coffee-100 p-4">
                {{ $pesanan->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
