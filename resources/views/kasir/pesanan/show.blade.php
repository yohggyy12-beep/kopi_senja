@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $pesanan->nomor_pesanan)
@section('page-title', 'Detail Pesanan #' . $pesanan->nomor_pesanan)

@section('content')
<div class="space-y-6">

    {{-- HEADER BAR: TOMBOL KEMBALI & STATUS --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('kasir.pesanan.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-coffee-900 transition hover:bg-cream-100">
            <span>←</span>
            Kembali
        </a>

        <div>
            @if ($pesanan->status === 'baru')
                <span class="rounded-full bg-cream-200 px-4 py-1.5 text-xs font-bold text-coffee-900">
                    Status: Baru
                </span>
            @elseif ($pesanan->status === 'diproses')
                <span class="rounded-full bg-amber-100 px-4 py-1.5 text-xs font-bold text-amber-900">
                    Status: Diproses
                </span>
            @elseif ($pesanan->status === 'selesai')
                <span class="rounded-full bg-green-100 px-4 py-1.5 text-xs font-bold text-status-success">
                    Status: Selesai
                </span>
            @else
                <span class="rounded-full bg-red-100 px-4 py-1.5 text-xs font-bold text-status-danger">
                    Status: Dibatalkan
                </span>
            @endif
        </div>
    </div>

    {{-- CONTENT GRID --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.6fr_1fr]">

        {{-- KOLOM KIRI: ITEM PESANAN (SNAPSHOT HARGA) --}}
        <div class="overflow-hidden rounded-2xl border border-coffee-100 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-coffee-100 px-6 py-4">
                <h2 class="text-sm font-bold text-ink-900">Item Pesanan (Snapshot Harga)</h2>
                <span class="text-xs font-medium text-muted">
                    {{ $pesanan->nomor_meja ? 'Meja M-' . sprintf('%02d', $pesanan->nomor_meja) : 'Takeaway' }} • {{ ucfirst($pesanan->metode_pembayaran) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-cream-100 uppercase tracking-wider text-muted font-bold text-[10px]">
                        <tr>
                            <th class="px-6 py-3.5">Menu</th>
                            <th class="px-6 py-3.5 text-center">Qty</th>
                            <th class="px-6 py-3.5">Harga Satuan</th>
                            <th class="px-6 py-3.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-100 text-ink-900">
                        @foreach ($pesanan->detailPesanan as $item)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-ink-900">
                                    {{ $item->nama_menu_saat_pemesanan }}
                                </td>
                                <td class="px-6 py-4 text-center font-medium">
                                    {{ $item->jumlah }}
                                </td>
                                <td class="px-6 py-4 text-muted">
                                    Rp {{ number_format($item->harga_satuan_saat_pemesanan, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-coffee-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-coffee-100 bg-cream-50/50 p-4 text-[11px] text-muted">
                Dibuat oleh kasir: <span class="font-semibold text-ink-900">{{ $pesanan->dibuatOlehPengguna->nama ?? '-' }}</span> pada {{ $pesanan->created_at->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        {{-- KOLOM KANAN: RINGKASAN & AKSI STATUS --}}
        <div class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm self-start space-y-5">
            <h3 class="text-sm font-bold text-ink-900 border-b border-coffee-100 pb-3">Ringkasan & Aksi Status</h3>

            <div class="rounded-xl bg-cream-50/70 p-4 space-y-2.5 text-xs text-ink-900 border border-coffee-100/60">
                <div class="flex justify-between">
                    <span class="text-muted">Subtotal</span>
                    <span class="font-semibold">Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Biaya Layanan (5%)</span>
                    <span class="font-semibold">Rp {{ number_format($pesanan->biaya_layanan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Pajak (10%)</span>
                    <span class="font-semibold">Rp {{ number_format($pesanan->jumlah_pajak, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-coffee-200 pt-2 text-sm font-bold text-coffee-900">
                    <span>Total Bayar</span>
                    <span>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="space-y-2.5 pt-2">
                @if ($pesanan->status === 'baru')
                    <form action="{{ route('kasir.pesanan.status', $pesanan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="diproses">
                        <button type="submit"
                            class="w-full rounded-xl bg-coffee-900 py-3 text-xs font-bold text-white transition hover:bg-coffee-800 shadow-sm">
                            Proses Pesanan
                        </button>
                    </form>

                    {{-- TOMBOL PEMBATALAN PESANAN DENGAN MODAL --}}
                    <button type="button" onclick="bukaModalBatal()"
                        class="w-full rounded-xl border border-red-200 py-3 text-xs font-bold text-status-danger transition hover:bg-red-50">
                        Batalkan Pesanan
                    </button>
                @elseif ($pesanan->status === 'diproses')
                    <form action="{{ route('kasir.pesanan.status', $pesanan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit"
                            class="w-full rounded-xl bg-coffee-900 py-3 text-xs font-bold text-white transition hover:bg-coffee-800 shadow-sm">
                            Tandai Selesai
                        </button>
                    </form>
                @elseif ($pesanan->status === 'selesai')
                    <a href="{{ route('kasir.pesanan.struk', $pesanan->id) }}"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-coffee-900 py-3 text-xs font-bold text-white transition hover:bg-coffee-800 shadow-sm">
                        <span>🖨️</span>
                        Lihat / Cetak Struk
                    </a>
                @else
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-status-danger font-medium text-center">
                        Pesanan ini telah dibatalkan dan seluruh stok bahan telah dikembalikan ke gudang.
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

{{-- MODAL KONFIRMASI PEMBATALAN (PRD P-11) --}}
<div id="modalBatalPesanan" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl sm:p-8">
        <div class="flex items-center gap-3 text-status-danger">
            <span class="text-2xl">⚠️</span>
            <h3 class="text-base font-bold text-ink-900">Batalkan Pesanan?</h3>
        </div>

        <p class="mt-3 text-xs text-muted leading-relaxed">
            Apakah Anda yakin ingin membatalkan pesanan <span class="font-bold text-ink-900">#{{ $pesanan->nomor_pesanan }}</span>?
            Tindakan ini akan mengembalikan seluruh stok bahan baku sesuai catatan penggunaan bahan saat pesanan dibuat.
        </p>

        <form action="{{ route('kasir.pesanan.destroy', $pesanan->id) }}" method="POST" class="mt-6 flex justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="tutupModalBatal()"
                class="rounded-xl border border-coffee-200 px-5 py-2.5 text-xs font-semibold text-ink-900 hover:bg-cream-100">
                Jangan Batalkan
            </button>
            <button type="submit"
                class="rounded-xl bg-status-danger px-5 py-2.5 text-xs font-bold text-white hover:bg-red-800 shadow-sm">
                Ya, Batalkan Pesanan
            </button>
        </form>
    </div>
</div>

<script>
    function bukaModalBatal() {
        document.getElementById('modalBatalPesanan').classList.remove('hidden');
        document.getElementById('modalBatalPesanan').classList.add('flex');
    }
    function tutupModalBatal() {
        document.getElementById('modalBatalPesanan').classList.add('hidden');
        document.getElementById('modalBatalPesanan').classList.remove('flex');
    }
</script>
@endsection
