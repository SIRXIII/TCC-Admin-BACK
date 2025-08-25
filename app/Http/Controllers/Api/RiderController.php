<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiderResource;
use App\Models\Rider;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $riders = Rider::all();
            return $this->success(RiderResource::collection($riders), 'Riders retrieved successfully');
        } catch (\Exception $e) {

            return $this->error('Failed to retrieve riders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $rider = Rider::find($id);

            if (!$rider) {  
                return $this->error('Rider not found', 404);
            }

            return $this->success(new RiderResource($rider), 'Rider retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve rider: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
