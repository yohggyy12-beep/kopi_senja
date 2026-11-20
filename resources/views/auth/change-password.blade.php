@extends('layouts.app')

@section('title', 'Profil & Ganti Password')
@section('page-title', 'Profil & Ganti Password')

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_1.4fr]">

    {{-- KARTU PROFIL AKUN --}}
    <div class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm sm:p-8 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-coffee-900 text-xl font-bold text-white">
                    @php
                        $user = auth()->user();
                        $parts = preg_split('/\s+/', trim($user->nama ?? $user->nama_pengguna ?? 'KS'));
                        $initials = collect($parts)->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode('');
                    @endphp
                    {{ strtoupper($initials) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-ink-900">{{ $user->nama ?? $user->nama_pengguna }}</h2>
                    <span class="inline-flex items-center rounded-full bg-cream-200 px-3 py-0.5 text-xs font-semibold text-coffee-800">
                        Aktif • {{ ucfirst($user->peran) }}
                    </span>
                </div>
            </div>

            <div class="mt-8 space-y-4 text-sm">
                <div class="flex justify-between border-b border-coffee-100 pb-3">
                    <span class="text-muted">Nama Lengkap</span>
                    <span class="font-semibold text-ink-900">{{ $user->nama }}</span>
                </div>
                <div class="flex justify-between border-b border-coffee-100 pb-3">
                    <span class="text-muted">Username</span>
                    <span class="font-semibold text-ink-900">{{ $user->nama_pengguna }}</span>
                </div>
                <div class="flex justify-between border-b border-coffee-100 pb-3">
                    <span class="text-muted">Peran Sistem</span>
                    <span class="font-semibold text-ink-900">
                        {{ $user->peran === 'manager' ? 'Manajer Operasional' : ($user->peran === 'kasir' ? 'Kasir Operasional' : ucfirst($user->peran)) }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ url('/logout') }}" method="POST" class="mt-8">
            @csrf
            <button type="submit"
                class="w-full rounded-xl border border-coffee-200 py-3 text-center text-sm font-semibold text-ink-900 transition hover:bg-cream-100">
                Logout dari Sesi
            </button>
        </form>
    </div>

    {{-- KARTU FORM GANTI PASSWORD --}}
    <div class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm sm:p-8">
        <h3 class="text-lg font-bold text-ink-900">Ubah Password Akun</h3>
        <p class="mt-1 text-xs text-muted">Masukkan password lama lalu buat password baru minimal 8 karakter.</p>

        @if (session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4 text-xs text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-xs text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="kata_sandi_lama" class="mb-1.5 block text-xs font-semibold text-ink-900">Password Lama</label>
                <input id="kata_sandi_lama" name="kata_sandi_lama" type="password" required
                    class="w-full rounded-xl border border-coffee-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700"
                    placeholder="••••••••">
            </div>

            <div>
                <label for="kata_sandi_baru" class="mb-1.5 block text-xs font-semibold text-ink-900">Password Baru</label>
                <input id="kata_sandi_baru" name="kata_sandi_baru" type="password" required minlength="8"
                    class="w-full rounded-xl border border-coffee-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700"
                    placeholder="Minimal 8 karakter">
            </div>

            <div>
                <label for="kata_sandi_baru_confirmation" class="mb-1.5 block text-xs font-semibold text-ink-900">Konfirmasi Password Baru</label>
                <input id="kata_sandi_baru_confirmation" name="kata_sandi_baru_confirmation" type="password" required minlength="8"
                    class="w-full rounded-xl border border-coffee-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-coffee-700 focus:ring-1 focus:ring-coffee-700"
                    placeholder="Ulangi password baru">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="reset"
                    class="rounded-xl border border-coffee-200 px-5 py-2.5 text-xs font-semibold text-ink-900 transition hover:bg-cream-100">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-xl bg-coffee-900 px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-coffee-800">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
