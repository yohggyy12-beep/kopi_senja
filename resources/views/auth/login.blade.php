<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>login - Kopi senja</title>
</head>

<body>
    <form action="{{ route('login.store') }}" method="POST">
        @csrf

        <div>
            <label>Nama Pengguna</label>
            <input name="nama_pengguna" type="text" value="{{ old('nama_pengguna') }}" required>
            @error('nama_pengguna')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label>Password</label>
            <input name="kata_sandi" type="password" required>
             @error('kata_sandi')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit">Log in</button>
    </form>
</body>

</html>
