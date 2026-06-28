<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiAutofillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiSuggestController extends Controller
{
    public function suggest(Request $request, AiAutofillService $service): JsonResponse
    {
        $request->validate([
            'client_id' => 'nullable|string|uuid',
            'current_items' => 'array',
            'current_items.*.name' => 'nullable|string|max:255',
        ]);

        $company = $request->user()->companies()->first();

        if (! $company) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = $service->suggestLineItems(
            $company->id,
            $request->input('client_id'),
            $request->input('current_items', [])
        );

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Return top 5 most-used line items for a client (from invoice history, no AI).
     * Used for instant "smart autofill" when a client is selected.
     */
    public function clientTopItems(Request $request, string $clientId): JsonResponse
    {
        $company = $request->user()->companies()->first();

        if (! $company) {
            return response()->json(['items' => []]);
        }

        $items = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.company_id', $company->id)
            ->where('invoices.client_id', $clientId)
            ->whereIn('invoices.status', ['paid', 'sent', 'viewed', 'overdue'])
            ->select(
                'invoice_items.name',
                'invoice_items.name_en',
                'invoice_items.unit',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('ROUND(AVG(invoice_items.unit_price), 2) as avg_price'),
                DB::raw('MAX(invoice_items.unit_price) as max_price'),
                DB::raw('MIN(invoice_items.unit_price) as min_price'),
            )
            ->groupBy('invoice_items.name', 'invoice_items.name_en', 'invoice_items.unit')
            ->orderByDesc('usage_count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'id' => uniqid('top_', true),
                'product_id' => null,
                'name' => $row->name,
                'name_en' => $row->name_en,
                'description' => null,
                'quantity' => 1,
                'unit' => $row->unit,
                'unit_price' => (float) $row->avg_price,
                'avg_price' => (float) $row->avg_price,
                'min_price' => (float) $row->min_price,
                'max_price' => (float) $row->max_price,
                'usage_count' => (int) $row->usage_count,
                'discount_percent' => 0,
                'line_total' => (float) $row->avg_price,
            ]);

        return response()->json(['items' => $items]);
    }
}
