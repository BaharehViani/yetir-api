<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
    use HasFactory, HasUlids;

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }
}
