<?php

namespace App\Http\Controllers;

use App\Support\BnpbDisaster;

class DisasterController extends Controller
{
    // JSON for the home "Informasi Bencana" card (loaded by JS, so a slow BNPB never delays the page).
    public function show()
    {
        $events = BnpbDisaster::recent();

        return response()->json([
            'regions' => BnpbDisaster::REGIONS,
            'days' => BnpbDisaster::HISTORY_DAYS,
            'events' => $events,
            'stats' => BnpbDisaster::stats($events),
            'updated' => BnpbDisaster::fetchedAt(),
            'source' => 'BNPB (Badan Nasional Penanggulangan Bencana)',
        ])->header('Cache-Control', BnpbDisaster::fetchedAt() ? 'public, max-age=600' : 'no-store');
    }
}
