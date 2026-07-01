<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    // ── Company Settings ──────────────────────────────────────────────────────

    public function company(): Response
    {
        $company = auth()->user()->companies()->first();
        return Inertia::render('Settings/Company', ['company' => $company]);
    }

    public function updateCompany(Request $request)
    {
        $company = auth()->user()->companies()->first();

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'name_en'           => 'nullable|string|max:255',
            'address'           => 'nullable|string',
            'address_en'        => 'nullable|string',
            'tax_id'            => 'nullable|string|size:13',
            'phone'             => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:255',
            'brand_color'       => 'nullable|string|size:7',
            'bank_name'         => 'nullable|string|max:100',
            'bank_account'      => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'logo'              => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $company->clearMediaCollection('logo');
            $company->addMediaFromRequest('logo')->toMediaCollection('logo');
            $validated['logo_url'] = $company->getFirstMediaUrl('logo');
        }

        unset($validated['logo']);
        $company->update($validated);

        return back()->with('success', 'บันทึกข้อมูลบริษัทแล้ว');
    }

    // ── Invoice Settings ──────────────────────────────────────────────────────

    public function invoice(): Response
    {
        $company = auth()->user()->companies()->first();
        return Inertia::render('Settings/Invoice', [
            'company' => $company->only([
                'id', 'invoice_prefix', 'invoice_next_number',
                'default_vat_rate', 'default_currency',
            ]),
        ]);
    }

    public function updateInvoice(Request $request)
    {
        $company = auth()->user()->companies()->first();

        $validated = $request->validate([
            'invoice_prefix'      => 'required|string|max:10|alpha_dash',
            'invoice_next_number' => 'required|integer|min:1',
            'default_vat_rate'    => 'required|numeric|in:0,7',
            'default_currency'    => 'required|string|in:THB,USD,EUR,SGD',
        ]);

        $company->update($validated);

        return back()->with('success', 'บันทึกการตั้งค่า invoice แล้ว');
    }

    // ── Account Settings ──────────────────────────────────────────────────────

    public function account(): Response
    {
        return Inertia::render('Settings/Account', [
            'user' => auth()->user()->only(['id', 'name', 'email', 'created_at']),
        ]);
    }

    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'บันทึกข้อมูลบัญชีแล้ว');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|min:8|confirmed',
        ]);

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'เปลี่ยนรหัสผ่านแล้ว');
    }

    public function deleteAccount(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $user = auth()->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'ลบบัญชีแล้ว ขอบคุณที่ใช้บริการ');
    }
}
