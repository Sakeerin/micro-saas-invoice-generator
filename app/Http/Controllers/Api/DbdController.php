<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbtLookupService;
use Illuminate\Http\Request;

class DbdController extends Controller
{
    public function lookup(Request $request, DbtLookupService $dbdService)
    {
        $request->validate([
            'tax_id' => 'required|string|size:13',
        ]);

        $data = $dbdService->lookup($request->tax_id);

        if (!$data) {
            return response()->json(['message' => 'Company not found or API error.'], 404);
        }

        return response()->json($data);
    }
}
