<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Dashboard') - Kedai Kopi Senja
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body>

    <div class="min-h-screen">

        {{-- SIDEBAR --}}
        @include('components.sidebar')


        {{-- OVERLAY MOBILE --}}
        <button id="sidebarOverlay" type="button" aria-label="Tutup navigasi"
            class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden"></button>


        {{-- KONTEN UTAMA --}}
        <div class="min-h-screen lg:ml-64">

            {{-- NAVBAR --}}
            @include('components.navbar')


            <main class="p-5 sm:p-6 lg:p-8">

                {{-- NOTIFIKASI BERHASIL --}}
                @if (session('success'))
                    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">

                        {{ session('success') }}

                    </div>
                @endif


                {{-- NOTIFIKASI GAGAL --}}
                @if (session('error'))
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                        {{ session('error') }}

                    </div>
                @endif


                {{-- VALIDASI --}}
                @if ($errors->any())

                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">

                        <p class="mb-2 font-semibold">
                            Periksa kembali data yang dimasukkan:
                        </p>

                        <ul class="list-inside list-disc space-y-1">

                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- ISI HALAMAN --}}
                @yield('content')

            </main>

        </div>

    </div>

</body>

</html>
