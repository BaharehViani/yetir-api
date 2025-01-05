<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class courier_info extends Model
{
    //
    use HasFactory, HasUuids;

    protected $table = 'courier_info';
    protected $with = ['user'];
}
