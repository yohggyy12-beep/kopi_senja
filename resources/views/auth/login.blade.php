<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kedai Kopi Senja</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-cream-50 px-4 py-10 text-ink-900">
    <main class="w-full max-w-md rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-semibold text-coffee-700">Kedai Kopi Senja</p>
        <h1 class="mt-2 text-2xl font-bold text-coffee-900">Masuk ke akun</h1>
        <p class="mt-2 text-sm text-muted">Gunakan nama pengguna dan kata sandi Anda.</p>

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="nama_pengguna" class="mb-2 block text-sm font-medium">Nama pengguna</label>
                <input id="nama_pengguna" name="nama_pengguna" type="text"
                    value="{{ old('nama_pengguna') }}" autocomplete="username" required autofocus
                    class="w-full rounded-lg border border-coffee-200 px-3 py-2.5 outline-none focus:border-coffee-600 focus:ring-2 focus:ring-coffee-100">
            </div>

            <div>
                <label for="kata_sandi" class="mb-2 block text-sm font-medium">Kata sandi</label>
                <input id="kata_sandi" name="kata_sandi" type="password"
                    autocomplete="current-password" required
                    class="w-full rounded-lg border border-coffee-200 px-3 py-2.5 outline-none focus:border-coffee-600 focus:ring-2 focus:ring-coffee-100">
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-coffee-900 px-4 py-3 font-semibold text-white transition hover:bg-coffee-800">
                Masuk
            </button>
        </form>
    </main>
</body>
</html>
