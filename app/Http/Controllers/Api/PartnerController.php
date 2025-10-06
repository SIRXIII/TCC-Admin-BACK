<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Models\PartnerDocument;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PartnerController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all partners.
     */
    public function index()
    {
        try {
            $partners = Partner::with('products', 'orders')->get();
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
            $partners = Partner::with('products', 'orders', 'documents')->find($id);

            if (!$partners) {
                return $this->error('Partner not found', 404);
            }

            return $this->success(new PartnerResource($partners), 'Partner retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve partners: ' . $e->getMessage(), 500);
        }
    }

    public function statusUpdate(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        $action = $request->status;
        $status = $action == 'accept' ? 'active' : 'suspended';

        Partner::find($request->id)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => "Partner {$status} successfully"
        ]);
    }

    public function sendEmail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'to' => 'required|email',
            'businessName' => 'required|string',
            'message' => 'required|string',
            'deadline' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();

        try {

            Mail::to($data['to'])->send(new RequestInformationEmail(
                $data['businessName'],
                $data['message'],
                $data['deadline']
            ));

            return $this->success(null, 'Email sent successfully', 200);
        } catch (\Exception $e) {

            return $this->error('Failed to send email', null, 500);
        }
    }


    public function store(Request $request)
    {
        $rules = [
            'businessName'      => "required",
            'email'             => 'required|email:rfc,dns|unique:partners,email',
            'phone'             => ['required', 'regex:/^[0-9-]{7,20}$/'],
            'ownerName'         => "required",
            'days'              => "required",
            'store_end_time'    => 'required',
            'store_start_time'  => 'required',
            'address'           => "required",
            'location'          => 'required',
            'tax_id'            => 'required|integer',
            'profileImage'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'licenseImages.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'ownerIdImages.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error('validation failed', $validator->errors(), 422);
        }

        $days = $request->days;

        if (is_string($days)) {

            $dayArray = array_map('trim', explode(',', $days));

            $dayArray = array_map('ucfirst', $dayArray);

            if (count($dayArray) === 1) {
                $daysFormatted = $dayArray[0];
            } else {

                $daysFormatted = "{$dayArray[0]} - " . end($dayArray);
            }
        } else {
            $daysFormatted = '';
        }

        $partner = Partner::create([
            "name"                => $request->ownerName,
            "business_name"       => $request->businessName,
            "email"               => $request->email,
            "phone"               => $request->phone,
            "category"            => $request->businesstype,
            "location"            => $request->location,
            "address"             => $request->address,
            "store_available_days" => $daysFormatted,
            "store_start_time"    => $request->store_available_start_time,
            "store_end_time"      => $request->store_available_end_time,
            "tax_id"              => $request->tax_id,
            "status"              => "active",
        ]);

        if ($request->hasFile('profileImage')) {
            $path = $request->file('profileImage')->store('partners/profile', 'hetzner');
            $partner->update(['profile_photo' => $path]);
        }

        if ($request->hasFile('license_front')) {
            $path = $request->file('license_front')->store('partners/licenses', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'license',
                'side'       => 'front',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('license_back')) {
            $path = $request->file('license_back')->store('partners/licenses', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'license',
                'side'       => 'back',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('ownerId_front')) {
            $path = $request->file('ownerId_front')->store('partners/owner_ids', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'owner_id',
                'side'       => 'front',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('ownerId_back')) {
            $path = $request->file('ownerId_back')->store('partners/owner_ids', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'owner_id',
                'side'       => 'back',
                'file_path'  => $path,
            ]);
        }

        return $this->success(null, 'Partner created successfully', 200);
    }

    public function update(Request $request, $id)
    {

        $partner = Partner::find($id);

        if (!$partner) {
            return $this->error('Partner not found', [], 404);
        }

        $rules = [
            'businessName'      => "required",
            'email'             => 'required|email:rfc,dns|unique:partners,email,' . $partner->id,
            'phone'             => ['required', 'regex:/^[0-9-]{7,20}$/'],
            'ownerName'         => "required",
            'days'              => "required",
            'store_end_time'    => 'required',
            'store_start_time'  => 'required',
            'address'           => "required",
            'location'          => 'required',
            'tax_id'            => 'required|integer',
            'profileImage'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'licenseImages.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'ownerIdImages.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $days = $request->days;

        if (is_array($days)) {
            
            $days = implode(',', $days);
        } elseif (is_object($days)) {
            $days = json_encode($days);
        }

        if (is_string($days)) {
            $days = trim($days);

            $days = str_replace(['[', ']', '"', "'"], '', $days);

            $days = preg_replace('/\s*-\s*/', ',', $days);

            $days = preg_replace('/\s*,\s*/', ',', $days);
        }


        $dayArray = array_filter(array_map('trim', explode(',', $days)));
        $dayArray = array_map('ucfirst', $dayArray);


        if (count($dayArray) === 0) {
            $daysFormatted = '';
        } elseif (count($dayArray) === 1) {
            $daysFormatted = $dayArray[0];
        } else {
            $daysFormatted = "{$dayArray[0]} - " . end($dayArray);
        }



        $partner->update([
            "name"                => $request->ownerName,
            "business_name"       => $request->businessName,
            "email"               => $request->email,
            "phone"               => $request->phone,
            "category"            => $request->businesstype,
            "location"            => $request->location,
            "address"             => $request->address,
            "store_available_days" => $daysFormatted,
            "store_start_time"    => $request->store_start_time,
            "store_end_time"      => $request->store_end_time,
            "tax_id"              => $request->tax_id,
            "status"              => "active",
        ]);

        if ($request->hasFile('profileImage')) {
            if ($partner->profile_photo && str_starts_with($partner->profile_photo, 'partners/profile/')) {
                if (Storage::disk('hetzner')->exists($partner->profile_photo)) {
                    Storage::disk('hetzner')->delete($partner->profile_photo);
                }
            }

            $path = $request->file('profileImage')->store('partners/profile', 'hetzner');
            $partner->update(['profile_photo' => $path]);
        }

        if ($request->hasFile('license_front')) {
            $existingFront = PartnerDocument::where('partner_id', $partner->id)
                ->where('type', 'license')
                ->where('side', 'front')
                ->first();
            if ($existingFront && Storage::disk('hetzner')->exists($existingFront->file_path)) {
                Storage::disk('hetzner')->delete($existingFront->file_path);
                $existingFront->delete();
            }
            $path = $request->file('license_front')->store('partners/licenses', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'license',
                'side'       => 'front',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('license_back')) {
            $existingBack = PartnerDocument::where('partner_id', $partner->id)
                ->where('type', 'license')
                ->where('side', 'back')
                ->first();
            if ($existingBack && Storage::disk('hetzner')->exists($existingBack->file_path)) {
                Storage::disk('hetzner')->delete($existingBack->file_path);
                $existingBack->delete();
            }
            $path = $request->file('license_back')->store('partners/licenses', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'license',
                'side'       => 'back',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('ownerId_front')) {
            $existingFront = PartnerDocument::where('partner_id', $partner->id)
                ->where('type', 'owner_id')
                ->where('side', 'front')
                ->first();
            if ($existingFront && Storage::disk('hetzner')->exists($existingFront->file_path)) {
                Storage::disk('hetzner')->delete($existingFront->file_path);
                $existingFront->delete();
            }
            $path = $request->file('ownerId_front')->store('partners/owner_ids', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'owner_id',
                'side'       => 'front',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('ownerId_back')) {
            $existingBack = PartnerDocument::where('partner_id', $partner->id)
                ->where('type', 'owner_id')
                ->where('side', 'back')
                ->first();
            if ($existingBack && Storage::disk('hetzner')->exists($existingBack->file_path)) {
                Storage::disk('hetzner')->delete($existingBack->file_path);
                $existingBack->delete();
            }
            $path = $request->file('ownerId_back')->store('partners/owner_ids', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'owner_id',
                'side'       => 'back',
                'file_path'  => $path,
            ]);
        }

        return $this->success(null, 'Partner updated successfully', 200);
    }

    public function downloadDocuments($partnerId)
    {
        $documents = PartnerDocument::where('partner_id', $partnerId)->get();

        if ($documents->isEmpty()) {
            return response()->json(['error' => 'No documents found'], 404);
        }

        $zip = new ZipArchive;
        $zipFileName = "partner_{$partnerId}_documents.zip";
        $zipPath = storage_path("app/temp/{$zipFileName}");

        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0777, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($documents as $doc) {
                $relativePath = $doc->file_path;

                if (Storage::disk('hetzner')->exists($relativePath)) {

                    $fileContents = Storage::disk('hetzner')->get($relativePath);


                    $tempFilePath = storage_path("app/temp/" . basename($relativePath));
                    file_put_contents($tempFilePath, $fileContents);


                    $zip->addFile($tempFilePath, basename($relativePath));
                }
            }
            $zip->close();
        } else {
            return response()->json(['error' => 'Could not create zip file'], 500);
        }

        if (!file_exists($zipPath)) {
            return response()->json(['error' => 'Zip file not created'], 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
