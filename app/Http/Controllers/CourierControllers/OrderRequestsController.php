<?php

namespace App\Http\Controllers\CourierControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderRequestsController extends Controller
{
    //
    public function index(Request $request) {
        return OrderRequest::where('status', 'pending')->get();
    }
}
