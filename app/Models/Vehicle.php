<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    //
    use HasFactory, HasUlids;

    public function user() {
        return $this->belongsTo(User::class);
    }
}
