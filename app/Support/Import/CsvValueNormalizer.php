<?php

namespace App\Support\Import;

<<<<<<< HEAD
use Illuminate\Support\Str;
=======
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
use InvalidArgumentException;

class CsvValueNormalizer
{
    /**
<<<<<<< HEAD
     * Menyeragamkan nama bahan.
     */
    public static function normalizeName(string $name): string
    {
        return (string) Str::of($name)
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', ' ');
    }

    /**
     * Mengubah kuantitas menjadi satuan dasar.
     */
    public static function toBaseUnit(
        float $quantity,
        string $unit
    ): array {
        $unit = strtolower(trim($unit));

        [$baseUnit, $factor] = match ($unit) {
            'kg' => ['g', 1000],
            'liter', 'l' => ['ml', 1000],
            'g' => ['g', 1],
            'ml' => ['ml', 1],
            'pcs' => ['pcs', 1],

            default => throw new InvalidArgumentException(
                "Satuan tidak dikenali: {$unit}"
            ),
        };

        return [
            'base_unit' => $baseUnit,
            'quantity' => round($quantity * $factor, 3),
            'factor' => $factor,
            // Kompatibilitas alias
            'unit' => $baseUnit,
            'value' => round($quantity * $factor, 3),
=======
     * Peta default konversi satuan ke satuan dasar proyek.
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
     * Menormalkan nama (huruf kecil, membuang spasi berlebih di awal, akhir, dan tengah).
     */
    public static function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }

    /**
     * Mengonversi takaran / stok ke satuan dasar sistem ('g', 'ml', 'pcs').
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
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
        ];
    }

    /**
<<<<<<< HEAD
     * Mengubah harga menjadi harga per satuan dasar.
     */
    public static function pricePerBaseUnit(
        float $price,
        float $factor
    ): float {
        return round($price / $factor, 4);
    }

    /**
     * Mencari nama padanan bahan baku dari daftar alias.
=======
     * Menghitung harga per satuan dasar berdasarkan faktor konversi.
     * Contoh: Rp 180.000 / kg dengan faktor 1000 => Rp 180 / g.
     */
    public static function pricePerBaseUnit(float $price, float $factor): float
    {
        if ($factor <= 0) {
            return (float) $price;
        }

        return (float) ($price / $factor);
    }

    /**
     * Mencari padanan nama bahan dari daftar alias.
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
     */
    public static function resolveAlias(string $name, ?array $aliases = null): string
    {
        $normalized = self::normalizeName($name);
<<<<<<< HEAD
        $aliasMap = $aliases ?? config('import.ingredient_aliases', config('import.aliases', []));
=======
        $aliasMap = $aliases ?? config('import.aliases', []);
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2

        return $aliasMap[$normalized] ?? $normalized;
    }
}
