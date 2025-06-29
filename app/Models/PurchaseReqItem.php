<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReqItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'item_name', 'description', 'quantity', 'estimated_price'
    ];

    public function requisition()
    {
        return $this->belongsTo(PurchaseReq::class, 'purchase_requisition_id');
    }
}
