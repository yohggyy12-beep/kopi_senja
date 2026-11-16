<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pesanan')->unique();

            $table->foreignId('dibuat_oleh_pengguna_id')
            ->constrained('users')
            ->restrictOnDelete();

            $table->unsignedTinyInteger('nomor_meja')->nullable();
            $table->enum('metode_pembayaran', ['tunai', 'qris']);

            $table->enum('status', ['baru', 'diproses', 'selesai', 'dibatalkan'])
            ->default('baru');

            $table->decimal('subtotal', 14, 2);
            $table->decimal('biaya_layanan', 14, 2);
            $table->decimal('jumlah_pajak', 14, 2);
            $table->decimal('total_harga', 14, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
