<?php

namespace App\Support\Import;

use Illuminate\Support\Str;
use InvalidArgumentException;

class CsvValueNormalizer
{
    /**
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
        ];
    }

    /**
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
     */
    public static function resolveAlias(string $name, ?array $aliases = null): string
    {
        $normalized = self::normalizeName($name);
        $aliasMap = $aliases ?? config('import.ingredient_aliases', config('import.aliases', []));

        return $aliasMap[$normalized] ?? $normalized;
    }
}
