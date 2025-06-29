<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReq extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'department_id',
        'pr_no',
        'req_date',
        'status',
        'remarks',
        'total_estimated_price'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseReqItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'pr_id');
    }
    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrd::class, 'pr_id');
    }
}
