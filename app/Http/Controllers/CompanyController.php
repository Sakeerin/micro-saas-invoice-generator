<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function wizard()
    {
        if (Auth::user()->companies()->exists()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Company/Wizard');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'address_en' => 'nullable|string',
            'tax_id' => 'nullable|string|size:13',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'bank_account_name' => 'nullable|string',
            'brand_color' => 'nullable|string|size:7',
            'invoice_prefix' => 'nullable|string|max:10',
            'logo' => 'nullable|image|max:2048',
        ]);

        $company = Auth::user()->companies()->create($validated);

        if ($request->hasFile('logo')) {
            $company->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
            
            $company->update(['logo_url' => $company->getFirstMediaUrl('logo')]);
        }

        // Create default free subscription
        Auth::user()->subscription()->create([
            'plan' => 'free',
        ]);

        return redirect()->route('dashboard');
    }
}
