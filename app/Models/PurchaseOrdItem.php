<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrdItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id', 'item_name', 'description', 'quantity',  'price'
    ];

    public function order()
    {
        return $this->belongsTo(PurchaseOrd::class, 'purchase_order_id');
    }
}
