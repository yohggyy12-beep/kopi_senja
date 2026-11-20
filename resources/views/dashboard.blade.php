@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <section class="rounded-2xl border border-coffee-100 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-medium text-muted">Kedai Kopi Senja</p>
        <h2 class="mt-2 text-2xl font-bold text-coffee-900">
            Selamat datang, {{ auth()->user()->nama }}.
        </h2>
        <p class="mt-2 text-sm text-muted">
            Anda masuk sebagai {{ auth()->user()->peran }}.
        </p>
    </section>
@endsection
