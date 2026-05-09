<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Track email opening via 1x1 pixel.
     */
    public function pixel(string $token)
    {
        $invoice = Invoice::where('share_token', $token)->first();

        if ($invoice && !$invoice->email_opened_at) {
            $invoice->update([
                'email_opened_at' => now(),
            ]);
        }

        // Return a 1x1 transparent GIF
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
