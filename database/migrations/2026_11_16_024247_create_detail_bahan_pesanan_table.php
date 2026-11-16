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
        Schema::create('detail_bahan_pesanan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('detail_pesanan_id')
                ->constrained('detail_pesanan')
                ->cascadeOnDelete();

            $table->foreignId('bahan_baku_id')
                ->constrained('bahan_baku')
                ->restrictOnDelete();

            $table->decimal('jumlah_digunakan', 14, 3);
            $table->timestamps();
            $table->unique(['detail_pesanan_id', 'bahan_baku_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_bahan_pesanan');
    }
};
