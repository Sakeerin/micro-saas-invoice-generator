<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DbtLookupService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.dbd.api_key');
        $this->apiUrl = config('services.dbd.api_url', 'https://openapi.dbd.go.th');
    }

    /**
     * Lookup company info from DBD by Tax ID (13 digits)
     */
    public function lookup(string $taxId): ?array
    {
        // Mock data for development/testing
        if (app()->environment('local')) {
            if ($taxId === '0000000000001') {
                return [
                    'name' => 'บริษัท ตัวอย่าง จำกัด',
                    'name_en' => 'Example Company Co., Ltd.',
                    'address' => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร 10110',
                    'tax_id' => '0000000000001',
                ];
            }
        }

        if (!$this->apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(3) // 3 seconds timeout as per plan
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->get($this->apiUrl . '/lookup/' . $taxId);

            if ($response->successful()) {
                return $this->formatResponse($response->json());
            }
        } catch (\Exception $e) {
            Log::error('DBD Lookup failed: ' . $e->getMessage());
        }

        return null;
    }

    protected function formatResponse(array $data): array
    {
        // Map DBD API response to our application's format
        return [
            'name' => $data['name_th'] ?? '',
            'name_en' => $data['name_en'] ?? '',
            'address' => $data['address'] ?? '',
            'tax_id' => $data['tax_id'] ?? '',
        ];
    }
}
