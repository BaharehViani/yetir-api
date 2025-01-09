<?php

namespace App\Http\Controllers\CourierControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderRequestsController extends Controller
{
    //
    public function index(Request $request) {
        return OrderRequest::where('status', 'pending')
        ->where('weight', '<=' ,$request->user()->vehicles()->first()->maximum_capacity)
        ->get();
    }
}
