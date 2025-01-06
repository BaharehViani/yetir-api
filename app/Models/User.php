<?php

namespace App\Models;

use App\Models\Order;
use App\Models\CourierInfo;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUlids, HasApiTokens;

    protected $casts = [
        'phone_verified_at' => 'datetime'
    ];

    protected $hidden = [
        'password'
    ];

    public function orderRequests(){
        return $this->hasMany(OrderRequest::class);
    }

    public function vehicles() {
        return $this->hasMany(Vehicle::class);
    }

    public function courierinfo() {
        return $this->hasOne(CourierInfo::class);
    }

    public function courierorders(): HasManyThrough {
        return $this->hasManyThrough(
            Order::class, 
            CourierInfo::class,
            'user_id',
            'courier_id',
            'id',
            'id'
        );
    }

    public function customerorders(): HasManyThrough {
        return $this->hasManyThrough(
            Order::class, 
            OrderRequest::class,
            'user_id',
            'order_request_id',
            'id',
            'id'
        );
    }
}