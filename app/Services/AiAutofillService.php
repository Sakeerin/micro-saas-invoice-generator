<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAutofillService
{
    private const MAX_HISTORY_INVOICES = 10;

    public function suggestLineItems(string $companyId, ?string $clientId, array $currentItems = []): array
    {
        $context = $this->buildContext($companyId, $clientId);
        $prompt = $this->buildPrompt($context, $currentItems);

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        if (! $response->successful()) {
            Log::error('Claude API error in AiAutofillService', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        return $this->parseResponse($response->json('content.0.text', ''));
    }

    private function buildContext(string $companyId, ?string $clientId): array
    {
        $context = [
            'client_history' => [],
            'product_catalog' => [],
            'price_memory' => [],
        ];

        if ($clientId) {
            $historicalInvoices = Invoice::where('company_id', $companyId)
                ->where('client_id', $clientId)
                ->whereIn('status', ['paid', 'sent', 'viewed'])
                ->with('items')
                ->latest('issue_date')
                ->limit(self::MAX_HISTORY_INVOICES)
                ->get();

            $context['client_history'] = $historicalInvoices->map(fn ($invoice) => [
                'date' => $invoice->issue_date,
                'total' => $invoice->total,
                'items' => $invoice->items->map(fn ($item) => [
                    'name' => $item->name,
                    'name_en' => $item->name_en,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ])->toArray(),
            ])->toArray();
        }

        $context['product_catalog'] = Product::where('company_id', $companyId)
            ->orderBy('name')
            ->limit(30)
            ->get(['name', 'name_en', 'unit', 'unit_price', 'description'])
            ->toArray();

        $context['price_memory'] = $this->buildPriceMemory($companyId);

        return $context;
    }

    /**
     * Aggregate average prices per service name across all company invoices.
     * Gives Claude accurate price baselines rather than guessing.
     */
    private function buildPriceMemory(string $companyId): array
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.company_id', $companyId)
            ->whereIn('invoices.status', ['paid', 'sent', 'viewed', 'overdue'])
            ->where('invoice_items.unit_price', '>', 0)
            ->select(
                'invoice_items.name',
                'invoice_items.unit',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('ROUND(AVG(invoice_items.unit_price), 2) as avg_price'),
                DB::raw('MIN(invoice_items.unit_price) as min_price'),
                DB::raw('MAX(invoice_items.unit_price) as max_price'),
            )
            ->groupBy('invoice_items.name', 'invoice_items.unit')
            ->orderByDesc('usage_count')
            ->limit(20)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'unit' => $row->unit,
                'usage_count' => (int) $row->usage_count,
                'avg_price' => (float) $row->avg_price,
                'min_price' => (float) $row->min_price,
                'max_price' => (float) $row->max_price,
            ])
            ->toArray();
    }

    private function buildPrompt(array $context, array $currentItems): string
    {
        $historyText = '';
        if (! empty($context['client_history'])) {
            $historyText = '**Client Invoice History (last '.count($context['client_history'])." invoices):**\n";
            foreach ($context['client_history'] as $inv) {
                $historyText .= "- Invoice dated {$inv['date']} (Total: {$inv['total']}):\n";
                foreach ($inv['items'] as $item) {
                    $nameEn = $item['name_en'] ? " / {$item['name_en']}" : '';
                    $historyText .= "  * {$item['name']}{$nameEn} - {$item['quantity']} {$item['unit']} × {$item['unit_price']} = {$item['line_total']}\n";
                }
            }
        }

        $catalogText = '';
        if (! empty($context['product_catalog'])) {
            $catalogText = "**Available Products/Services:**\n";
            foreach ($context['product_catalog'] as $product) {
                $nameEn = $product['name_en'] ? " / {$product['name_en']}" : '';
                $catalogText .= "- {$product['name']}{$nameEn} ({$product['unit']}, price: {$product['unit_price']})\n";
            }
        }

        $currentItemsText = '';
        $filledItems = array_filter($currentItems, fn ($item) => ! empty($item['name']));
        if (! empty($filledItems)) {
            $currentItemsText = "**Items Already Added (do not suggest duplicates):**\n";
            foreach ($filledItems as $item) {
                $currentItemsText .= "- {$item['name']}\n";
            }
        }

        $priceMemoryText = '';
        if (! empty($context['price_memory'])) {
            $priceMemoryText = "**Price Memory (your historical rates — use these for accurate pricing):**\n";
            foreach ($context['price_memory'] as $entry) {
                $range = $entry['min_price'] !== $entry['max_price']
                    ? "฿{$entry['min_price']}–฿{$entry['max_price']}"
                    : "฿{$entry['avg_price']}";
                $priceMemoryText .= "- {$entry['name']} ({$entry['unit']}): avg ฿{$entry['avg_price']} [{$range}], used {$entry['usage_count']}×\n";
            }
        }

        $hasContext = ! empty($context['client_history']) || ! empty($context['product_catalog']) || ! empty($context['price_memory']);
        $fallbackNote = $hasContext ? '' : "Note: No history available. Suggest common Thai freelancer services (web dev, design, content).\n\n";

        return <<<PROMPT
You are an AI assistant helping a Thai freelancer/business create invoices. Based on the context below, suggest relevant line items for a new invoice.

{$historyText}
{$catalogText}
{$priceMemoryText}
{$currentItemsText}
{$fallbackNote}
Please suggest 3-5 relevant line items. Prioritize items from client history, use price memory for accurate pricing.

Respond ONLY with a valid JSON array (no markdown, no explanation, no code fences). Each item must have:
- "name": Thai name (string, required)
- "name_en": English name (string or null)
- "quantity": suggested quantity (number, default 1)
- "unit": unit of measure in Thai (string, e.g. "งาน", "ชิ้น", "ชั่วโมง", "เดือน")
- "unit_price": price in THB based on price memory if available (number)
- "description": brief detail (string or null)

Example: [{"name":"พัฒนาเว็บไซต์","name_en":"Website Development","quantity":1,"unit":"งาน","unit_price":50000,"description":null}]
PROMPT;
    }

    private function parseResponse(string $responseText): array
    {
        $responseText = trim($responseText);

        // Strip markdown code fences if present
        if (preg_match('/```(?:json)?\s*([\s\S]+?)\s*```/', $responseText, $matches)) {
            $responseText = $matches[1];
        }

        $data = json_decode($responseText, true);

        if (! is_array($data)) {
            Log::warning('AiAutofillService: failed to parse Claude response as JSON', [
                'response' => $responseText,
            ]);

            return [];
        }

        return array_values(array_filter(
            array_map(fn ($item) => $this->normalizeItem($item), $data),
            fn ($item) => ! empty($item['name'])
        ));
    }

    private function normalizeItem(mixed $item): array
    {
        if (! is_array($item)) {
            return [];
        }

        $quantity = max(0.01, (float) ($item['quantity'] ?? 1));
        $unitPrice = max(0, (float) ($item['unit_price'] ?? 0));

        return [
            'id' => uniqid('ai_', true),
            'product_id' => null,
            'name' => (string) ($item['name'] ?? ''),
            'name_en' => isset($item['name_en']) && $item['name_en'] !== '' ? (string) $item['name_en'] : null,
            'description' => isset($item['description']) && $item['description'] !== '' ? (string) $item['description'] : null,
            'quantity' => $quantity,
            'unit' => (string) ($item['unit'] ?? 'งาน'),
            'unit_price' => $unitPrice,
            'discount_percent' => 0,
            'line_total' => round($quantity * $unitPrice, 2),
        ];
    }
}
