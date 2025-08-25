<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all partners.
     */
    public function index()
    {
        try {
            $partners = Partner::get();
            return $this->success(PartnerResource::collection($partners), 'Partners retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve partners: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Fetch a single partners by ID.
     */
    public function show($id)
    {
        try {
            $partners = Partner::find($id);

            if (!$partners) {
                return $this->error('Partner not found', 404);
            }

            return $this->success(new PartnerResource($partners), 'Partner retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve partners: ' . $e->getMessage(), 500);
        }
    }
}
