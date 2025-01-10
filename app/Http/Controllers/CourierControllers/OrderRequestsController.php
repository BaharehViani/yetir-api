<?php

namespace App\Http\Controllers\CourierControllers;

use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderRequestsController extends Controller
{
    //
    public function index(Request $request) {
        return OrderRequest::where('status', 'pending')
        ->where('weight', '<=' ,$request->user()->vehicles()->first()->maximum_capacity)
        ->get();
    }
}
