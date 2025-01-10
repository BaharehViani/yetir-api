<?php

namespace App\Models;

use App\Models\User;
use App\Models\CourierInfo;
use App\Models\OrderRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Order extends Model
{
    //
    use HasFactory, HasUlids;

    protected $with = ['orderRequest', 'courierinfo'];

    
    public function courierinfo() {
        return $this->belongsTo(CourierInfo::class, 'courier_id');
    }

    public function orderRequest() {
        return $this->belongsTo(OrderRequest::class);
    }

    public function user(): HasOneThrough {
        return $this->hasOneThrough(
            User::class,
            CourierInfo::class,
            'user_id',
            'courier_id',
            'id',
            'id'    
        );
    }
}
