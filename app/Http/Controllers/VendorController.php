<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::all();

        return response()->json([
            'success' => true,
            'data' => $vendors
        ]);
    }

    public function get_select()
    {
        $vendors = Vendor::all()->map(function ($dept) {
            return [
                'label' => $dept->vendor_name,
                'value' => $dept->id,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $vendors
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:255',
            'contact_person' => 'nullable|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|max:50',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vendor = Vendor::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Vendor created successfully.',
            'data' => $vendor
        ], 201);
    }

    public function show($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vendor
        ]);
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:255',
            'contact_person' => 'nullable|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|max:50',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vendor->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully.',
            'data' => $vendor
        ]);
    }

    public function destroy($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.'
            ], 404);
        }

        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted successfully.'
        ]);
    }
}
