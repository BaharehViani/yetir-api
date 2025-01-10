<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    use HasFactory, HasUlids;

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
}
