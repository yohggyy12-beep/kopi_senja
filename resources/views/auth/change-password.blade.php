<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ganti Password</title>
</head>

<body>
    <h1>Ganti Password</h1>
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>Kata sandi lama</label>
            <!-- Diubah dari type="text" ke type="password" -->
            <input name="kata_sandi_lama" type="password" required>
            @error('kata_sandi_lama')
                <p class="error" style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>Kata sandi baru</label>
            <!-- Diubah dari type="text" ke type="password" -->
            <input name="kata_sandi_baru" type="password" required>
            @error('kata_sandi_baru')
                <p class="error" style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>Konfirmasi kata sandi baru</label>
            <!-- Diubah dari type="text" ke type="password" -->
            <input name="kata_sandi_baru_confirmation" type="password" required>
        </div>

        <button type="submit">Simpan Password</button>

    </form>
</body>

</html>
