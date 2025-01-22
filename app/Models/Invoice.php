<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    //
    use HasFactory, HasUlids;

    protected $with = ['order'];

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
