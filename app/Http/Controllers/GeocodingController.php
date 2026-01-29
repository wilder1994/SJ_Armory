<?php

namespace App\Http\Controllers;

use App\Services\GeocodingService;
use Illuminate\Http\Request;

class GeocodingController extends Controller
{
    public function reverse(Request $request, GeocodingService $geocodingService)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $result = $geocodingService->reverseGeocode((float) $data['lat'], (float) $data['lng']);

        if (!$result) {
            return response()->json([
                'error' => 'reverse_geocode_failed',
            ], 422);
        }

        return response()->json($result);
    }
}
