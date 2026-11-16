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
        Schema::create('pembelian_stok', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bahan_baku_id')
                ->constrained('bahan_baku')
                ->restrictOnDelete();

            $table->foreignId('dicatat_oleh_pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('jumlah', 14, 3);
            $table->date('tanggal_pembelian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_stok');
    }
};
