@extends('layouts.app')

@section('title', 'Buat Pesanan Kasir')
@section('page-title', 'Pemesanan Kasir')

@section('content')
<div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.6fr_1fr]">

    {{-- KOLOM KIRI: KATALOG MENU --}}
    <div class="space-y-6">

        {{-- SEARCH & FILTER KATEGORI --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-coffee-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <input type="text" id="searchInput"
                    placeholder="Cari Kopi Susu, Americano, Cappuccino..."
                    class="w-full rounded-xl border border-coffee-200 bg-cream-50/50 px-4 py-2.5 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:bg-white focus:ring-1 focus:ring-coffee-700">
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="filterContainer">
                <button type="button" data-filter="all"
                    class="filter-btn shrink-0 rounded-full bg-coffee-900 px-4 py-2 text-xs font-semibold text-white transition">
                    Semua
                </button>
                @foreach ($kategoriList as $kat)
                    <button type="button" data-filter="{{ Str::slug($kat) }}"
                        class="filter-btn shrink-0 rounded-full border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-ink-900 transition hover:bg-cream-100">
                        {{ ucfirst($kat) }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- GRID KARTU MENU --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" id="menuGrid">
            @forelse ($menus as $menu)
                <div class="menu-item flex flex-col justify-between rounded-2xl border border-coffee-100 bg-white p-5 shadow-sm transition hover:shadow-md"
                    data-id="{{ $menu->id }}"
                    data-nama="{{ $menu->nama }}"
                    data-harga="{{ $menu->harga_jual }}"
                    data-kategori="{{ Str::slug($menu->kategori) }}"
                    data-tersedia="{{ $menu->is_tersedia ? '1' : '0' }}">

                    <div class="flex items-start gap-3.5">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-cream-200 text-2xl shadow-inner">
                            @if (stripos($menu->kategori, 'makanan') !== false)
                                🥐
                            @else
                                ☕
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            @if ($menu->is_tersedia)
                                <span class="inline-flex rounded-full bg-green-50 border border-green-200 px-2.5 py-0.5 text-[10px] font-semibold text-status-success">
                                    Tersedia
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-50 border border-red-200 px-2.5 py-0.5 text-[10px] font-semibold text-status-danger">
                                    Habis
                                </span>
                            @endif

                            <h3 class="mt-1.5 truncate text-sm font-bold text-ink-900">
                                {{ $menu->nama }}
                            </h3>
                            <p class="text-xs text-muted">
                                {{ ucfirst($menu->kategori) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-coffee-100/60 pt-3.5">
                        <span class="text-sm font-bold text-coffee-900">
                            Rp {{ number_format($menu->harga_jual, 0, ',', '.') }}
                        </span>

                        @if ($menu->is_tersedia)
                            <button type="button"
                                onclick="tambahItem({{ $menu->id }}, '{{ addslashes($menu->nama) }}', {{ $menu->harga_jual }})"
                                class="rounded-xl bg-coffee-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-coffee-800 active:scale-95">
                                Tambah
                            </button>
                        @else
                            <button type="button" disabled
                                class="cursor-not-allowed rounded-xl bg-cream-200 px-4 py-2 text-xs font-semibold text-muted">
                                Habis
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center">
                    <p class="text-sm font-semibold text-ink-900">Belum ada menu yang aktif.</p>
                </div>
            @endforelse
        </div>

    </div>

    {{-- KOLOM KANAN: KERANJANG PESANAN --}}
    <div class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm flex flex-col justify-between self-start sticky top-24">
        <div>
            <div class="border-b border-coffee-100 pb-4">
                <h2 class="text-base font-bold text-ink-900">Keranjang Pesanan</h2>
                <p class="text-xs text-muted mt-0.5">Daftar menu yang dipesan pelanggan</p>
            </div>

            {{-- LIST ITEM DI KERANJANG --}}
            <div id="cartList" class="my-4 divide-y divide-coffee-100 max-h-[300px] overflow-y-auto pr-1">
                <div id="emptyCart" class="py-12 text-center">
                    <span class="text-3xl">🛒</span>
                    <p class="mt-2 text-xs font-medium text-muted">Keranjang masih kosong</p>
                    <p class="text-[11px] text-muted">Klik "Tambah" pada menu di sebelah kiri</p>
                </div>
            </div>

            <form id="orderForm" action="{{ route('kasir.pesanan.store') }}" method="POST" class="space-y-4 pt-2 border-t border-coffee-100">
                @csrf
                <div id="hiddenInputs"></div>

                {{-- PILIH METODE PEMBAYARAN & NOMOR MEJA --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="metode_pembayaran" class="mb-1 block text-xs font-medium text-ink-900">Pembayaran</label>
                        <select id="metode_pembayaran" name="metode_pembayaran" required
                            class="w-full rounded-xl border border-coffee-200 bg-white px-3 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                            <option value="tunai">Tunai</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div>
                        <label for="nomor_meja" class="mb-1 block text-xs font-medium text-ink-900">Nomor Meja</label>
                        <input id="nomor_meja" name="nomor_meja" type="number" min="1" max="10"
                            placeholder="Meja 1 - 10"
                            class="w-full rounded-xl border border-coffee-200 px-3 py-2 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700">
                    </div>
                </div>

                {{-- RINCIAN BIAYA --}}
                <div class="rounded-xl bg-cream-50/70 p-4 space-y-2 text-xs text-ink-900 border border-coffee-100/60">
                    <div class="flex justify-between">
                        <span class="text-muted">Subtotal</span>
                        <span id="labelSubtotal" class="font-semibold">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Biaya Layanan (5%)</span>
                        <span id="labelLayanan" class="font-semibold">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Pajak (10%)</span>
                        <span id="labelPajak" class="font-semibold">Rp 0</span>
                    </div>
                    <div class="flex justify-between border-t border-coffee-200 pt-2 text-sm font-bold text-coffee-900">
                        <span>Total Pembayaran</span>
                        <span id="labelTotal">Rp 0</span>
                    </div>
                </div>

                {{-- TOMBOL SUBMIT --}}
                <button type="submit" id="btnSubmitOrder" disabled
                    class="w-full cursor-not-allowed rounded-xl bg-cream-200 py-3.5 text-xs font-bold text-muted transition">
                    Pilih Menu Terlebih Dahulu
                </button>
            </form>
        </div>
    </div>

</div>

<script>
    let cart = [];

    function tambahItem(id, nama, harga) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.jumlah += 1;
        } else {
            cart.push({ id, nama, harga, jumlah: 1 });
        }
        renderCart();
    }

    function kurangiItem(id) {
        const index = cart.findIndex(item => item.id === id);
        if (index !== -1) {
            if (cart[index].jumlah > 1) {
                cart[index].jumlah -= 1;
            } else {
                cart.splice(index, 1);
            }
        }
        renderCart();
    }

    function hapusItem(id) {
        cart = cart.filter(item => item.id !== id);
        renderCart();
    }

    function formatRupiah(angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }

    function renderCart() {
        const cartList = document.getElementById('cartList');
        const hiddenInputs = document.getElementById('hiddenInputs');
        const btnSubmit = document.getElementById('btnSubmitOrder');

        hiddenInputs.innerHTML = '';

        if (cart.length === 0) {
            cartList.innerHTML = `
                <div id="emptyCart" class="py-12 text-center">
                    <span class="text-3xl">🛒</span>
                    <p class="mt-2 text-xs font-medium text-muted">Keranjang masih kosong</p>
                    <p class="text-[11px] text-muted">Klik "Tambah" pada menu di sebelah kiri</p>
                </div>
            `;
            document.getElementById('labelSubtotal').textContent = 'Rp 0';
            document.getElementById('labelLayanan').textContent = 'Rp 0';
            document.getElementById('labelPajak').textContent = 'Rp 0';
            document.getElementById('labelTotal').textContent = 'Rp 0';

            btnSubmit.disabled = true;
            btnSubmit.className = 'w-full cursor-not-allowed rounded-xl bg-cream-200 py-3.5 text-xs font-bold text-muted transition';
            btnSubmit.textContent = 'Pilih Menu Terlebih Dahulu';
            return;
        }

        let subtotal = 0;
        let html = '';

        cart.forEach((item, index) => {
            const itemTotal = item.harga * item.jumlah;
            subtotal += itemTotal;

            html += `
                <div class="flex items-center justify-between py-3">
                    <div class="min-w-0 flex-1 pr-2">
                        <p class="truncate text-xs font-bold text-ink-900">${item.nama}</p>
                        <p class="text-[11px] text-muted">${formatRupiah(item.harga)} / porsi</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center rounded-lg border border-coffee-200 bg-white">
                            <button type="button" onclick="kurangiItem(${item.id})"
                                class="flex h-7 w-7 items-center justify-center text-xs font-bold text-coffee-900 transition hover:bg-cream-100">
                                -
                            </button>
                            <span class="w-7 text-center text-xs font-semibold text-ink-900">${item.jumlah}</span>
                            <button type="button" onclick="tambahItem(${item.id}, '${item.nama.replace(/'/g, "\\'")}', ${item.harga})"
                                class="flex h-7 w-7 items-center justify-center text-xs font-bold text-coffee-900 transition hover:bg-cream-100">
                                +
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Hidden inputs untuk submit Laravel Form Request
            hiddenInputs.innerHTML += `
                <input type="hidden" name="items[${index}][menu_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][jumlah]" value="${item.jumlah}">
            `;
        });

        cartList.innerHTML = html;

        // Hitung Layanan 5% & Pajak 10%
        const layanan = Math.round(subtotal * 0.05);
        const pajak = Math.round((subtotal + layanan) * 0.10);
        const total = subtotal + layanan + pajak;

        document.getElementById('labelSubtotal').textContent = formatRupiah(subtotal);
        document.getElementById('labelLayanan').textContent = formatRupiah(layanan);
        document.getElementById('labelPajak').textContent = formatRupiah(pajak);
        document.getElementById('labelTotal').textContent = formatRupiah(total);

        btnSubmit.disabled = false;
        btnSubmit.className = 'w-full rounded-xl bg-coffee-900 py-3.5 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800 active:scale-[0.99] cursor-pointer';
        btnSubmit.textContent = 'Buat Pesanan (' + formatRupiah(total) + ')';
    }

    // Filter kategori & search
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const menuItems = document.querySelectorAll('.menu-item');

        let activeFilter = 'all';

        function applyFilter() {
            const query = searchInput.value.toLowerCase().trim();

            menuItems.forEach(item => {
                const nama = item.dataset.nama.toLowerCase();
                const kategori = item.dataset.kategori;

                const matchQuery = nama.includes(query);
                const matchCategory = (activeFilter === 'all') || (kategori === activeFilter);

                if (matchQuery && matchCategory) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', applyFilter);

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                filterBtns.forEach(b => {
                    b.className = 'filter-btn shrink-0 rounded-full border border-coffee-200 bg-white px-4 py-2 text-xs font-semibold text-ink-900 transition hover:bg-cream-100';
                });
                this.className = 'filter-btn shrink-0 rounded-full bg-coffee-900 px-4 py-2 text-xs font-semibold text-white transition';
                activeFilter = this.dataset.filter;
                applyFilter();
            });
        });
    });
</script>
@endsection
