<?php

namespace Tests\Unit;

use App\Services\TaxCalculationInput;
use App\Services\TaxEngineService;
use PHPUnit\Framework\TestCase;

class TaxEngineServiceTest extends TestCase
{
    private TaxEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaxEngineService();
    }

    public function test_vat_only_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 1, 'unit_price' => 1000, 'discount_percent' => 0]
            ],
            vatRate: 7.0,
            whtRate: 0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(1000.0, $result->subtotal);
        $this->assertEquals(70.0, $result->vatAmount);
        $this->assertEquals(0.0, $result->whtAmount);
        $this->assertEquals(1070.0, $result->total);
    }

    public function test_wht_only_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 1, 'unit_price' => 1000, 'discount_percent' => 0]
            ],
            vatRate: 0,
            whtRate: 3.0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(1000.0, $result->subtotal);
        $this->assertEquals(0.0, $result->vatAmount);
        $this->assertEquals(30.0, $result->whtAmount);
        $this->assertEquals(970.0, $result->total);
    }

    public function test_vat_and_wht_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 2, 'unit_price' => 500, 'discount_percent' => 0]
            ],
            vatRate: 7.0,
            whtRate: 3.0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(1000.0, $result->subtotal);
        $this->assertEquals(70.0, $result->vatAmount);
        $this->assertEquals(30.0, $result->whtAmount);
        $this->assertEquals(1040.0, $result->total); // 1000 + 70 - 30
    }

    public function test_discount_percent_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 1, 'unit_price' => 1000, 'discount_percent' => 0]
            ],
            discountType: 'percent',
            discountValue: 10,
            vatRate: 7.0,
            whtRate: 3.0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(1000.0, $result->subtotal);
        $this->assertEquals(100.0, $result->discountAmount);
        $this->assertEquals(900.0, $result->subtotalAfterDiscount);
        $this->assertEquals(63.0, $result->vatAmount); // 900 * 0.07
        $this->assertEquals(27.0, $result->whtAmount); // 900 * 0.03
        $this->assertEquals(936.0, $result->total); // 900 + 63 - 27
    }

    public function test_discount_amount_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 1, 'unit_price' => 1000, 'discount_percent' => 0]
            ],
            discountType: 'amount',
            discountValue: 200,
            vatRate: 7.0,
            whtRate: 3.0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(1000.0, $result->subtotal);
        $this->assertEquals(200.0, $result->discountAmount);
        $this->assertEquals(800.0, $result->subtotalAfterDiscount);
        $this->assertEquals(56.0, $result->vatAmount); // 800 * 0.07
        $this->assertEquals(24.0, $result->whtAmount); // 800 * 0.03
        $this->assertEquals(832.0, $result->total); // 800 + 56 - 24
    }

    public function test_zero_value_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 0, 'unit_price' => 1000, 'discount_percent' => 0]
            ],
            vatRate: 7.0,
            whtRate: 3.0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(0.0, $result->subtotal);
        $this->assertEquals(0.0, $result->total);
    }

    public function test_precision_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 1, 'unit_price' => 33.33, 'discount_percent' => 0],
                ['quantity' => 1, 'unit_price' => 33.33, 'discount_percent' => 0],
                ['quantity' => 1, 'unit_price' => 33.33, 'discount_percent' => 0]
            ],
            vatRate: 7.0,
            whtRate: 0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(99.99, $result->subtotal);
        $this->assertEquals(7.0, $result->vatAmount); // round(99.99 * 0.07, 2) = round(6.9993, 2) = 7.00
        $this->assertEquals(106.99, $result->total);
    }

    public function test_line_item_discount_calculation()
    {
        $input = new TaxCalculationInput(
            items: [
                ['quantity' => 2, 'unit_price' => 500, 'discount_percent' => 10] // 1000 - 100 = 900
            ],
            vatRate: 7.0,
            whtRate: 0
        );

        $result = $this->service->calculate($input);

        $this->assertEquals(900.0, $result->subtotal);
        $this->assertEquals(63.0, $result->vatAmount);
        $this->assertEquals(963.0, $result->total);
    }
}
