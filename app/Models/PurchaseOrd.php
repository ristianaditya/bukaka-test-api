<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrd extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id', 'pr_id', 'po_date', 'delivery_date', 'total_price', 'status'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function requisition()
    {
        return $this->belongsTo(PurchaseReq::class, 'pr_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrdItem::class, 'purchase_order_id');
    }
}
