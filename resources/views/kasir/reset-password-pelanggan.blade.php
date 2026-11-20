@extends('layouts.app')

@section('title', 'Reset Password Pelanggan')
@section('page-title', 'Reset Password Pelanggan')

@section('content')
<div class="flex justify-center pt-4">

    <div class="w-full max-w-xl rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm sm:p-8">

        <div>
            <h2 class="text-base font-bold text-ink-900">Cari Akun Pelanggan</h2>
            <p class="mt-1 text-xs text-muted leading-relaxed">
                Masukkan nomor HP terdaftar untuk mengatur ulang password sementara. Pelanggan wajib mengganti password saat login berikutnya.
            </p>
        </div>

        {{-- FORM PENCARIAN NOMOR HP --}}
        <form method="GET" action="{{ route('kasir.pelanggan.reset-page') }}" class="mt-6 flex gap-2">
            <input type="text" name="nomor_telepon" value="{{ $nomorTelepon ?? '' }}" required
                placeholder="Contoh: 0812-3456-7890"
                class="flex-1 rounded-xl border border-coffee-200 bg-cream-50/50 px-4 py-2.5 text-xs text-ink-900 outline-none transition focus:border-coffee-700 focus:bg-white focus:ring-1 focus:ring-coffee-700">

            <button type="submit"
                class="rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-coffee-800 shrink-0">
                Cari Akun
            </button>
        </form>

        {{-- JIKA PENCARIAN DILAKUKAN --}}
        @if (!empty($nomorTelepon))
            @if ($pelanggan)
                {{-- KOTAK INFORMASI PELANGGAN DITEMUKAN --}}
                <div class="mt-6 rounded-xl border border-coffee-200 bg-cream-50/60 p-5 space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-ink-900">{{ $pelanggan->nama }}</h3>
                            <p class="mt-0.5 text-xs text-muted">
                                {{ $pelanggan->nomor_telepon ?? $pelanggan->nama_pengguna }} • Member Senja
                            </p>
                        </div>
                        <span class="rounded-full bg-green-100 px-3 py-1 text-[10px] font-bold text-status-success">
                            Ditemukan
                        </span>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-[11px] font-medium text-amber-900">
                        Wajib ganti password pada login berikutnya setelah reset dilakukan.
                    </div>

                    <form action="{{ route('kasir.pelanggan.reset-password', $pelanggan->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full rounded-xl bg-coffee-900 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-coffee-800">
                            Reset Password & Buat Sandi Sementara
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-center">
                    <p class="text-xs font-bold text-status-danger">Akun pelanggan tidak ditemukan.</p>
                    <p class="mt-1 text-[11px] text-muted">Pastikan nomor telepon terdaftar di aplikasi Android Kedai Kopi Senja.</p>
                </div>
            @endif
        @endif

    </div>

</div>

{{-- MODAL HASIL PASSWORD SEMENTARA (PRD P-13) --}}
@if (session('password_sementara'))
    <div id="modalHasilReset" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-8 text-center animate-in fade-in duration-200">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-2xl text-status-success">
                ✓
            </div>

            <h3 class="mt-4 text-base font-bold text-ink-900">Password Berhasil Direset</h3>
            <p class="mt-1 text-xs text-muted">
                Berikut adalah kata sandi sementara untuk pelanggan <span class="font-bold text-ink-900">{{ session('nama_pelanggan') }}</span>:
            </p>

            <div class="my-5 flex items-center justify-between rounded-xl border border-coffee-200 bg-cream-50 p-3.5">
                <span id="passwordText" class="font-mono text-base font-extrabold tracking-widest text-coffee-900">
                    {{ session('password_sementara') }}
                </span>
                <button type="button" onclick="salinPassword()" id="btnSalin"
                    class="rounded-lg bg-coffee-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-coffee-800">
                    Salin
                </button>
            </div>

            <p class="text-[11px] text-muted leading-relaxed">
                Berikan kata sandi ini kepada pelanggan. Pelanggan wajib membuat kata sandi baru saat pertama kali login.
            </p>

            <button type="button" onclick="document.getElementById('modalHasilReset').remove()"
                class="mt-6 w-full rounded-xl border border-coffee-200 py-2.5 text-xs font-semibold text-ink-900 hover:bg-cream-100">
                Selesai
            </button>
        </div>
    </div>

    <script>
        function salinPassword() {
            const pass = document.getElementById('passwordText').textContent.trim();
            navigator.clipboard.writeText(pass).then(() => {
                const btn = document.getElementById('btnSalin');
                btn.textContent = 'Tersalin!';
                btn.className = 'rounded-lg bg-green-700 px-3 py-1.5 text-xs font-semibold text-white';
                setTimeout(() => {
                    btn.textContent = 'Salin';
                    btn.className = 'rounded-lg bg-coffee-900 px-3 py-1.5 text-xs font-semibold text-white';
                }, 2000);
            });
        }
    </script>
@endif
@endsection
