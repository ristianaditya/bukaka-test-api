<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseOrdController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrd::with(['vendor', 'requisition', 'items'])->get();
        return response()->json(['success' => true, 'data' => $orders], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'pr_id' => 'required|exists:purchase_reqs,id',
            'po_date' => 'required|date',
            'delivery_date' => 'required|date',
            'total_price' => 'required|numeric',
            'status' => 'required|numeric',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = PurchaseOrd::create($validator->validated());

        foreach ($request->items as $item) {
            $order->items()->create($item);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order created successfully.',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'pr_id' => 'required|exists:purchase_reqs,id',
            'po_date' => 'required|date',
            'delivery_date' => 'required|date',
            'total_price' => 'required|numeric',
            'status' => 'required|numeric',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = PurchaseOrd::findOrFail($id);
        $order->update($validator->validated());

        $order->items()->delete();

        foreach ($request->items as $item) {
            $order->items()->create($item);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order updated successfully.',
        ]);
    }

    public function edit($id)
    {
        $order = PurchaseOrd::with([
            'vendor',
            'items',
            'requisition.user',
            'requisition.department',
        ])->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'PO not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $order], 200);
    }

    public function destroy($id)
    {
        $order = PurchaseOrd::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'PO not found'], 404);
        }

        $order->delete();

        return response()->json(['success' => true, 'message' => 'PO deleted'], 200);
    }
}
