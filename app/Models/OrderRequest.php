<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderRequest extends Model
{
    //
    use HasFactory, HasUlids;

    protected $table = 'order_requests';

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function order() {
        return $this->hasOne(Order::class);
    }
}
