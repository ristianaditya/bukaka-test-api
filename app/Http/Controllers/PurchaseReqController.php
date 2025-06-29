<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PurchaseReqController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'req_date' => 'required|date',
            'department' => 'nullable|numeric',
            'remarks' => 'nullable|string',
            'estimated_price' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.estimated_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $reqDate = $request->input('req_date');
        $departmentId = $request->input('department');
        $formattedDate = date('Ymd', strtotime($reqDate));
        $randomString = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        $prNo = $formattedDate . $departmentId . $randomString;
        $pr = PurchaseReq::create([
            'user_id' => $user->id,
            'department_id' => $request->input('department'),
            'req_date' => $request->input('req_date'),
            'pr_no' => $prNo,
            'remarks' => $request->input('remarks'),
            'total_estimated_price' => $request->input('estimated_price')
        ]);

        foreach ($request->items as $item) {
            $pr->items()->create($item);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Requisition created successfully.'
        ], 201);
    }

    public function index()
    {
        $requisitions = PurchaseReq::with(['user', 'department', 'approvals.approver'])
            ->get()
            ->map(function ($pr) {
                $managerApproval = null;
                $hodApproval = null;

                foreach ($pr->approvals as $approval) {
                    $role = $approval->approver?->role;
                    $status = $approval->status;

                    if ($role === 'manager') {
                        $managerApproval = $status;
                    } elseif ($role === 'hod') {
                        $hodApproval = $status;
                    }
                }

                $status_approval = null;

                if ($managerApproval === 'approved' && $hodApproval === 'approved') {
                    $status_approval = 2; // approved fully
                } elseif ($managerApproval === 'approved' && $hodApproval === 'rejected') {
                    $status_approval = 4; // rejected by HOD
                } elseif ($managerApproval === 'rejected') {
                    $status_approval = 3; // rejected by Manager
                } elseif ($managerApproval === 'approved') {
                    $status_approval = 1; // only approved by Manager
                }

                $pr->setAttribute('status_approval', $status_approval);
                return $pr;
            });

        return response()->json([
            'success' => true,
            'data' => $requisitions
        ], 200);
    }

    public function get_select()
    {
        $pr = PurchaseReq::where('status', 3)
            ->whereDoesntHave('purchaseOrder') // pastikan belum digunakan di purchase_ords
            ->get()
            ->map(function ($req) {
                return [
                    'label' => $req->pr_no,
                    'value' => $req->id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pr
        ]);
    }

    public function edit($id)
    {
        $requisition = PurchaseReq::with(['user', 'department', 'items', 'approvals.approver'])
            ->where('id', $id)
            ->first();

        if (!$requisition) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Requisition not found.'
            ], 404);
        }

        $managerApproval = null;
        $hodApproval = null;

        foreach ($requisition->approvals as $approval) {
            $role = $approval->approver?->role;
            $status = $approval->status;

            if ($role === 'manager') {
                $managerApproval = $status;
            } elseif ($role === 'hod') {
                $hodApproval = $status;
            }
        }

        $status_approval = null;

        if ($managerApproval === 'approved' && $hodApproval === 'approved') {
            $status_approval = 2; // Approved by both
        } elseif ($managerApproval === 'approved' && $hodApproval === 'rejected') {
            $status_approval = 4; // Rejected by HOD
        } elseif ($managerApproval === 'rejected') {
            $status_approval = 3; // Rejected by Manager
        } elseif ($managerApproval === 'approved') {
            $status_approval = 1; // Approved only by Manager
        }

        $requisition->setAttribute('status_approval', $status_approval);

        return response()->json([
            'success' => true,
            'data' => $requisition
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'req_date' => 'required|date',
            'department' => 'nullable|numeric',
            'remarks' => 'nullable|string',
            'estimated_price' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.estimated_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $requisition = PurchaseReq::with('items')
                        ->where('user_id', $user->id)
                        ->where('id', $id)
                        ->first();

        if (!$requisition) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Requisition not found.'
            ], 404);
        }

        $requisition->update([
            'department_id' => $request->input('department'),
            'req_date' => $request->input('req_date'),
            'remarks' => $request->input('remarks'),
            'total_estimated_price' => $request->input('estimated_price')
        ]);

        // Delete all existing items first
        $requisition->items()->delete();

        // Re-create with updated items
        foreach ($request->items as $item) {
            $requisition->items()->create($item);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Requisition updated successfully.',
        ], 200);
    }

    public function send($id)
    {
        $requisition = PurchaseReq::with('items')
                        ->where('id', $id)
                        ->first();

        if (!$requisition) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Requisition not found.'
            ], 404);
        }

        $requisition->update([
            'status' => '1',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Purchase Requisition sent successfully.',
        ], 200);
    }

    public function destroy($id)
    {
        $requisition = PurchaseReq::where('id', $id)
                        ->first();
        if (!$requisition) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Requisition not found.'
            ], 404);
        }
        $requisition->delete();
        return response()->json([
            'success' => true,
            'message' => 'Purchase Requisition deleted successfully.'
        ], 200);
    }
}
