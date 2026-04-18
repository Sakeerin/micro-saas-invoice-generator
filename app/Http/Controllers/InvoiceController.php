<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $company = $user->companies()->first();
        
        $invoices = Invoice::where('company_id', $company->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        $company = $user->companies()->first();
        $clients = Client::where('company_id', $company->id)->get();
        $products = Product::where('company_id', $company->id)->get();

        // Generate default invoice number
        $year = now()->year;
        $prefix = $company->invoice_prefix ?? 'INV';
        $nextNumber = str_pad($company->invoice_next_number ?? 1, 4, '0', STR_PAD_LEFT);
        $defaultInvoiceNumber = "{$prefix}-{$year}-{$nextNumber}";

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'products' => $products,
            'defaultInvoiceNumber' => $defaultInvoiceNumber,
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation will be added in a later step
        // For now, focus on UI and saving basic info
        
        $user = auth()->user();
        $company = $user->companies()->first();

        DB::transaction(function () use ($request, $company) {
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'currency' => $request->currency ?? $company->default_currency ?? 'THB',
                'client_name' => $request->client_name,
                'client_name_en' => $request->client_name_en,
                'client_address' => $request->client_address,
                'client_address_en' => $request->client_address_en,
                'client_tax_id' => $request->client_tax_id,
                // Add default values for required fields
                'subtotal' => 0,
                'total' => 0,
                'status' => 'draft',
            ]);

            // Increment company next invoice number if this one was used
            $company->increment('invoice_next_number');
        });

        return redirect()->route('invoices.index');
    }
}
