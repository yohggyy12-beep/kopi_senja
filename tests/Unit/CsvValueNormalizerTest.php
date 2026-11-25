<?php
<<<<<<< HEAD
namespace Tests\Unit;
use App\Support\Import\CsvValueNormalizer;
use Tests\TestCase;
class CsvValueNormalizerTest extends TestCase
{

=======

namespace Tests\Unit;

use App\Support\Import\CsvValueNormalizer;
use PHPUnit\Framework\TestCase;

class CsvValueNormalizerTest extends TestCase
{
    public function test_normalize_name(): void
    {
        $this->assertEquals(
            'biji kopi arabika',
            CsvValueNormalizer::normalizeName('  Biji  Kopi Arabika  ')
        );
    }

    public function test_to_base_unit(): void
    {
        $kg = CsvValueNormalizer::toBaseUnit(5, 'kg');
        $this->assertEquals(5000.0, $kg['value']);
        $this->assertEquals('g', $kg['unit']);
        $this->assertEquals(1000.0, $kg['factor']);

        $liter = CsvValueNormalizer::toBaseUnit(2, 'liter');
        $this->assertEquals(2000.0, $liter['value']);
        $this->assertEquals('ml', $liter['unit']);

        $pcs = CsvValueNormalizer::toBaseUnit(10, 'pcs');
        $this->assertEquals(10.0, $pcs['value']);
        $this->assertEquals('pcs', $pcs['unit']);
    }

    public function test_price_per_base_unit(): void
    {
        $price = CsvValueNormalizer::pricePerBaseUnit(180000, 1000);
        $this->assertEquals(180.0, $price);

        $pricePcs = CsvValueNormalizer::pricePerBaseUnit(500, 1);
        $this->assertEquals(500.0, $pricePcs);
    }

    public function test_resolve_alias(): void
    {
        $aliases = ['biji kopi arabica' => 'biji kopi arabika'];
        $resolved = CsvValueNormalizer::resolveAlias('Biji kopi Arabica', $aliases);
        $this->assertEquals('biji kopi arabika', $resolved);

        $unaliased = CsvValueNormalizer::resolveAlias('Susu UHT', $aliases);
        $this->assertEquals('susu uht', $unaliased);
    }
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
}
