<?php

namespace App\Http\Controllers\XUserControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return $request->user();
    }
}
