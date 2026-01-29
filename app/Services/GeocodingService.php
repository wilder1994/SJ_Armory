<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    public function geocode(string $address, ?string $city = null): ?array
    {
        $query = trim($address . ($city ? ', ' . $city : '') . ', Colombia');
        if ($query === '') {
            return null;
        }

        $response = Http::withHeaders([
            'User-Agent' => 'SJ_Armory/1.0 (contact@example.com)',
        ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
            'format' => 'json',
            'q' => $query,
            'countrycodes' => 'co',
            'limit' => 1,
        ]);

        if (!$response->ok()) {
            return null;
        }

        $result = $response->json()[0] ?? null;
        if (!$result || !isset($result['lat'], $result['lon'])) {
            return null;
        }

        return [
            'lat' => (float) $result['lat'],
            'lng' => (float) $result['lon'],
        ];
    }

    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'SJ_Armory/1.0 (contact@example.com)',
        ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
            'format' => 'jsonv2',
            'lat' => $lat,
            'lon' => $lng,
            'addressdetails' => 1,
        ]);

        if (!$response->ok()) {
            return null;
        }

        return $response->json();
    }
}
