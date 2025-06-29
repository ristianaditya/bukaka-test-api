<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\PurchaseReq;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ApprovalController extends Controller
{
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $role = $user->role;

        $requisitions = PurchaseReq::with(['user', 'department', 'items', 'approvals.approver'])
            ->where('status', '>=', 1)
            ->get()
            ->map(function ($pr) {
                $status_approval = null;

                $managerApproval = null;
                $hodApproval = null;

                foreach ($pr->approvals as $approval) {
                    $approverRole = $approval->approver?->role;
                    $approvalStatus = $approval->status;

                    if ($approverRole === 'manager') {
                        $managerApproval = $approvalStatus;
                    }

                    if ($approverRole === 'hod') {
                        $hodApproval = $approvalStatus;
                    }
                }

                // Evaluasi kombinasi status
                if ($managerApproval === 'approved' && $hodApproval === 'approved') {
                    $status_approval = 2;
                } elseif ($managerApproval === 'approved' && $hodApproval === 'rejected') {
                    $status_approval = 4;
                } elseif ($managerApproval === 'rejected') {
                    $status_approval = 3;
                } elseif ($managerApproval === 'approved') {
                    $status_approval = 1;
                }

                $pr->setAttribute('status_approval', $status_approval);
                return $pr;
            })
            ->filter(function ($pr) use ($role) {
                if ($role === 'hod') {
                    return $pr->status_approval !== null && $pr->status_approval >= 1 && $pr->status_approval != 3;
                }
                return true;
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $requisitions
        ], 200);
    }

    public function send($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $existing = Approval::where('pr_id', $id)
            ->where('approver_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already submitted an approval for this requisition.'
            ], 409);
        }

        $approval = Approval::create([
            'pr_id' => $id,
            'approver_id' => $user->id,
            'approval_date' => now(),
            'status' => 'approved'
        ]);

        $requisition = PurchaseReq::find($id);

        if (!$requisition) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Requisition not found.'
            ], 404);
        }

        // Cek role user, jika 'hod' maka set status = 3, selain itu = 2
        $newStatus = ($user->role === 'hod') ? 3 : 2;

        $requisition->update([
            'status' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval recorded successfully.',
            'data' => $approval
        ]);
    }

    public function reject($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $existing = Approval::where('pr_id', $id)
            ->where('approver_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already submitted an approval for this requisition.'
            ], 409);
        }

        $approval = Approval::create([
            'pr_id' => $id,
            'approver_id' => $user->id,
            'approval_date' => now(),
            'status' => 'rejected'
        ]);

        $requisition = PurchaseReq::where('id', $id)
                        ->first();

        if (!$requisition) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Requisition not found.'
            ], 404);
        }

        $requisition->update([
            'status' => '4',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval recorded successfully.',
            'data' => $approval
        ]);
    }
    public function history($id)
    {
        $approvals = Approval::with(['purchaseRequisition', 'approver'])->where('pr_id', $id)->get();

        return response()->json([
            'success' => true,
            'data' => $approvals
        ]);
    }
}
