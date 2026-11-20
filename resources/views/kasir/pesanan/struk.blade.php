@extends('layouts.app')

@section('title', 'Struk Pesanan #' . $pesanan->nomor_pesanan)
@section('page-title', 'Struk Pesanan')

@section('content')
<style>
    @media print {
        #appSidebar, header, .no-print, button, a {
            display: none !important;
        }
        body {
            background-color: white !important;
            padding: 0 !important;
        }
        main {
            padding: 0 !important;
        }
        .struk-card {
            box-shadow: none !important;
            border: none !important;
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>

<div class="space-y-6">

    {{-- NAVIGASI & TOMBOL CETAK --}}
    <div class="no-print flex items-center justify-between">
        <a href="{{ route('kasir.pesanan.show', $pesanan->id) }}"
            class="inline-flex items-center gap-2 rounded-xl border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-coffee-900 transition hover:bg-cream-100">
            <span>←</span>
            Kembali
        </a>

        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
            <span>🖨️</span>
            Cetak Struk
        </button>
    </div>

    {{-- KARTU STRUK THERMAL RESMI KOPI SENJA --}}
    <div class="flex justify-center">
        <div class="struk-card w-full max-w-md rounded-2xl border border-coffee-100 bg-white p-8 shadow-sm">

            {{-- HEADER STRUK --}}
            <div class="text-center">
                <h2 class="text-base font-extrabold tracking-wider text-ink-900 uppercase">Kedai Kopi Senja</h2>
                <p class="mt-1 text-xs text-muted">Jl. Senja No. 14, Jakarta Selatan</p>
                <p class="mt-1 text-[11px] text-muted">
                    #{{ $pesanan->nomor_pesanan }} • {{ $pesanan->created_at->translatedFormat('d M Y, H:i') }} WIB
                </p>
            </div>

            <div class="my-6 border-t border-dashed border-coffee-200"></div>

            {{-- DAFTAR ITEM --}}
            <div class="space-y-3.5 text-xs text-ink-900">
                @foreach ($pesanan->detailPesanan as $item)
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-ink-900">{{ $item->jumlah }}x {{ $item->nama_menu_saat_pemesanan }}</p>
                            <p class="text-[11px] text-muted">(@Rp {{ number_format($item->harga_satuan_saat_pemesanan, 0, ',', '.') }})</p>
                        </div>
                        <span class="font-bold text-coffee-900">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="my-6 border-t border-dashed border-coffee-200"></div>

            {{-- KALKULASI --}}
            <div class="space-y-2 text-xs text-ink-900">
                <div class="flex justify-between">
                    <span class="text-muted">Subtotal</span>
                    <span class="font-medium">Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Biaya Layanan (5%)</span>
                    <span class="font-medium">Rp {{ number_format($pesanan->biaya_layanan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Pajak (10%)</span>
                    <span class="font-medium">Rp {{ number_format($pesanan->jumlah_pajak, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-coffee-200 pt-3 text-sm font-extrabold text-coffee-900">
                    <span>Total Pembayaran</span>
                    <span>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="my-6 border-t border-dashed border-coffee-200"></div>

            {{-- FOOTER --}}
            <div class="text-center text-[11px] text-muted space-y-1">
                <p>Pembayaran: <span class="font-bold text-ink-900 uppercase">{{ $pesanan->metode_pembayaran }}</span> • Kasir: <span class="font-bold text-ink-900">{{ $pesanan->dibuatOlehPengguna->nama ?? 'Kasir' }}</span></p>
                @if($pesanan->nomor_meja)
                    <p>Nomor Meja: <span class="font-bold text-ink-900">M-{{ sprintf('%02d', $pesanan->nomor_meja) }}</span></p>
                @else
                    <p>Status: <span class="font-bold text-ink-900">Takeaway</span></p>
                @endif
                <p class="pt-2 font-medium">Terima kasih telah berkunjung ke Kedai Kopi Senja</p>
            </div>

        </div>
    </div>

</div>
@endsection
