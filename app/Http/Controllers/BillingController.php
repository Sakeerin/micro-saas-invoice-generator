<?php

namespace App\Http\Controllers;

use App\Services\OmiseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        return Inertia::render('Settings/Billing', [
            'subscription' => $subscription,
            'plans'        => OmiseService::PLANS,
            'omisePublicKey' => config('services.omise.public_key'),
        ]);
    }

    public function upgrade(Request $request, OmiseService $omise)
    {
        $request->validate([
            'plan'       => 'required|in:pro,business',
            'card_token' => 'required|string',
        ]);

        $user = auth()->user();

        try {
            $omise->upgradeUser($user, $request->plan, $request->card_token);
        } catch (\Exception $e) {
            return back()->withErrors(['card_token' => 'การชำระเงินล้มเหลว: ' . $e->getMessage()]);
        }

        return back()->with('success', 'อัปเกรดแผนสำเร็จ');
    }

    public function cancel(OmiseService $omise)
    {
        $user = auth()->user();

        try {
            $omise->downgradeToFree($user);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'ไม่สามารถยกเลิกได้: ' . $e->getMessage()]);
        }

        return back()->with('success', 'ยกเลิกแผนแล้ว จะกลับมาใช้แผน Free เมื่อสิ้นรอบ');
    }
}
