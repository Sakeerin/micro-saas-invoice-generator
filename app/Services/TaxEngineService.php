<?php

namespace App\Services;

class TaxCalculationInput
{
    public function __construct(
        public array $items,
        public string $discountType = 'none',
        public float $discountValue = 0,
        public float $vatRate = 7.0,
        public bool $vatIncluded = false,
        public float $whtRate = 0,
    ) {}
}

class TaxResult
{
    public function __construct(
        public float $subtotal,
        public float $discountAmount,
        public float $subtotalAfterDiscount,
        public float $vatRate,
        public float $vatAmount,
        public float $whtRate,
        public float $whtAmount,
        public float $total,
    ) {}

    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discountAmount,
            'subtotal_after_discount' => $this->subtotalAfterDiscount,
            'vat_rate' => $this->vatRate,
            'vat_amount' => $this->vatAmount,
            'wht_rate' => $this->whtRate,
            'wht_amount' => $this->whtAmount,
            'total' => $this->total,
        ];
    }
}

class TaxEngineService
{
    /**
     * คำนวณ tax ทั้งหมดจาก line items + config
     * Return immutable result object
     */
    public function calculate(TaxCalculationInput $input): TaxResult
    {
        // 1. คำนวณ subtotal จาก line items
        $subtotal = collect($input->items)->sum(function ($item) {
            // Support both array and object items
            $quantity = is_array($item) ? ($item['quantity'] ?? 0) : ($item->quantity ?? 0);
            $unitPrice = is_array($item) ? ($item['unit_price'] ?? 0) : ($item->unit_price ?? 0);
            $discountPercent = is_array($item) ? ($item['discount_percent'] ?? 0) : ($item->discount_percent ?? 0);

            $lineTotal = $quantity * $unitPrice;
            if ($discountPercent > 0) {
                $lineTotal *= (1 - $discountPercent / 100);
            }
            return round($lineTotal, 2);
        });

        // 2. คำนวณ discount ระดับ invoice
        $discountAmount = match ($input->discountType) {
            'percent' => round($subtotal * $input->discountValue / 100, 2),
            'amount'  => min($input->discountValue, $subtotal),
            default   => 0,
        };

        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // 3. VAT คำนวณบน subtotal หลัง discount
        //    กรณีราคารวม VAT แล้ว: vat = subtotal × rate / (100 + rate)
        //    กรณีราคายังไม่รวม VAT: vat = subtotal × rate / 100
        $vatAmount = $input->vatIncluded
            ? round($subtotalAfterDiscount * $input->vatRate / (100 + $input->vatRate), 2)
            : round($subtotalAfterDiscount * $input->vatRate / 100, 2);

        // 4. WHT คำนวณบน subtotal (ก่อน VAT ตามกฎหมายภาษีไทย)
        $whtAmount = round($subtotalAfterDiscount * $input->whtRate / 100, 2);

        // 5. Total = subtotal + VAT - WHT
        //    (ลูกค้าจ่ายให้เรา = ยอดรวม VAT แล้วหัก WHT ที่เขา remit แทนเรา)
        $total = $subtotalAfterDiscount + $vatAmount - $whtAmount;

        return new TaxResult(
            subtotal:               (float) $subtotal,
            discountAmount:         (float) $discountAmount,
            subtotalAfterDiscount:  (float) $subtotalAfterDiscount,
            vatRate:                (float) $input->vatRate,
            vatAmount:              (float) $vatAmount,
            whtRate:                (float) $input->whtRate,
            whtAmount:              (float) $whtAmount,
            total:                  round((float) $total, 2),
        );
    }
}
