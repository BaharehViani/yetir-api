<?php

namespace App\Http\Controllers\CourierControllers;

use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderRequestsController extends Controller
{
    public function index(Request $request) {
        $max_capacity = (float) $request->user()->vehicles()->first()->maximum_capacity;
        return OrderRequest::where('status', 'pending')
            ->whereRaw('CAST(weight AS DECIMAL(10,2)) <= ?', [$max_capacity])
            ->get();
    }
}
