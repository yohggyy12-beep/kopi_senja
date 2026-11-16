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
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_normalisasi')->unique();
            $table->string('kode_pemasok')->nullable()->unique();
            $table->enum('satuan_dasar', ['g', 'ml', 'pcs']);
            $table->decimal('jumlah_stok', 14, 3)->default(0);
            $table->decimal('stok_minimum', 14, 3)->default(0);
            $table->decimal('harga_per_satuan_dasar', 14, 4)->nullable();
            $table->date('tanggal_pembaruan_harga_pemasok')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku');
    }
};
