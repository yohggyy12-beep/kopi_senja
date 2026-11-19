<?php

namespace App\Support\Import;

use InvalidArgumentException;

class CsvValueNormalizer
{
    /**
     * Konfigurasi default konversi satuan ke satuan dasar.
     */
    protected const DEFAULT_UNIT_MAP = [
        'kg' => ['base' => 'g', 'factor' => 1000.0],
        'g' => ['base' => 'g', 'factor' => 1.0],
        'liter' => ['base' => 'ml', 'factor' => 1000.0],
        'l' => ['base' => 'ml', 'factor' => 1000.0],
        'ml' => ['base' => 'ml', 'factor' => 1.0],
        'pcs' => ['base' => 'pcs', 'factor' => 1.0],
    ];

    /**
     * Menormalkan nama (huruf kecil, menghapus spasi ganda dan spasi di ujung teks).
     */
    public static function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }

    /**
     * Mengonversi takaran atau stok ke satuan dasar ('g', 'ml', 'pcs').
     *
     * @return array{value: float, unit: string, factor: float}
     */
    public static function toBaseUnit(float $value, string $unit): array
    {
        $normalizedUnit = mb_strtolower(trim($unit));
        $unitMap = config('import.units', self::DEFAULT_UNIT_MAP);

        if (!isset($unitMap[$normalizedUnit])) {
            throw new InvalidArgumentException("Satuan '{$unit}' tidak dikenali dalam sistem.");
        }

        $base = $unitMap[$normalizedUnit]['base'];
        $factor = (float) $unitMap[$normalizedUnit]['factor'];

        return [
            'value' => (float) ($value * $factor),
            'unit' => $base,
            'factor' => $factor,
        ];
    }

    /**
     * Menghitung harga per satuan dasar berdasarkan faktor konversi satuan.
     * Contoh: Rp 180.000 / kg (faktor 1000) => Rp 180 / g.
     */
    public static function pricePerBaseUnit(float $price, float $factor): float
    {
        if ($factor <= 0) {
            return (float) $price;
        }

        return (float) ($price / $factor);
    }

    /**
     * Mencari nama padanan bahan baku dari daftar alias.
     */
    public static function resolveAlias(string $name, ?array $aliases = null): string
    {
        $normalized = self::normalizeName($name);
        $aliasMap = $aliases ?? config('import.aliases', []);

        return $aliasMap[$normalized] ?? $normalized;
    }
}
