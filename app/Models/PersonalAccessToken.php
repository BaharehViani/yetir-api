<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use HasUlids;
}
