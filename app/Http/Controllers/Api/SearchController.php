<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\RiderResource;
use App\Http\Resources\TravelerResource;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Rider;
use App\Models\Traveler;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
        if (!$query) {
            return response()->json([]);
        }

        $partners = Partner::where('business_name', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->get();

        $travelers = Traveler::where('name', 'like', "%{$query}%")->get();

        $riders = Rider::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->get();

        $partnerResults = PartnerResource::collection($partners)
            ->additional(['type' => 'Partner']);
        $travelerResults = TravelerResource::collection($travelers)
            ->additional(['type' => 'Traveler']);
        $riderResults = RiderResource::collection($riders)
            ->additional(['type' => 'Rider']);

        $results = collect()
            ->concat($partnerResults)
            ->concat($travelerResults)
            ->concat($riderResults)
            ->values();

        return response()->json($results);
    }
}
