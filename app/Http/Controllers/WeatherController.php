<?php

namespace App\Http\Controllers;

use App\Support\BmkgWeather;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    // JSON for the weather widgets (loaded by JS, so a slow BMKG never delays page rendering).
    public function show(Request $request)
    {
        $district = $request->query('kec', BmkgWeather::DEFAULT_DISTRICT);
        if (! is_string($district) || ! isset(BmkgWeather::DISTRICTS[$district])) {
            $district = BmkgWeather::DEFAULT_DISTRICT;
        }

        $slots = BmkgWeather::upcoming(BmkgWeather::forecast($district));
        $source = ($slots[0]['source'] ?? 'BMKG') === 'Open-Meteo'
            ? 'Open-Meteo (cadangan, data BMKG sedang tidak tersedia)'
            : 'BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)';

        return response()->json([
            'district' => $district,
            'name' => BmkgWeather::DISTRICTS[$district]['name'],
            'districts' => array_map(fn ($d) => $d['name'], BmkgWeather::DISTRICTS),
            'slots' => $slots,
            'alerts' => BmkgWeather::alerts(),
            'source' => $source,
        ])->header('Cache-Control', $slots ? 'public, max-age=600' : 'no-store'); // never pin an empty answer in caches
    }
}
