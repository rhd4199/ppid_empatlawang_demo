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

        return response()->json([
            'district' => $district,
            'name' => BmkgWeather::DISTRICTS[$district]['name'],
            'districts' => array_map(fn ($d) => $d['name'], BmkgWeather::DISTRICTS),
            'slots' => BmkgWeather::upcoming(BmkgWeather::forecast($district)),
            'alerts' => BmkgWeather::alerts(),
            'source' => 'BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)',
        ])->header('Cache-Control', 'public, max-age=600');
    }
}
