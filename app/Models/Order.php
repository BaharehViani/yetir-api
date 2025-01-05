<?php

namespace App\Models;

use App\Models\CourierInfo;
use App\Models\OrderRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    //
    use HasFactory, HasUlids;

    protected $with = ['request'];

    protected $fillable = [
        'pickupplace',
        'dropoffplace',
    ];


    public function courierinfo() {
        return $this->belongsTo(CourierInfo::class);
    }
    public function orderrequest() {
        return $this->belongsTo(OrderRequest::class);
    }
}
