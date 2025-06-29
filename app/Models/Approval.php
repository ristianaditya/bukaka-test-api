<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = ['pr_id', 'approver_id', 'approval_date', 'status'];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseReq::class, 'pr_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
