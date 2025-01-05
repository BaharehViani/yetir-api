<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Courier_info extends Model
{
    //
    use HasFactory, HasUlids;

    protected $table = 'courier_info';
    protected $with = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function orders() {
        return $this->hasMany(Order::class);
    }
}
