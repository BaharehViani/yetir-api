<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    //
    public function index(Request $request) {

        $request->validate([
            'status' => 'required|in:waiting_for_pickup,in_delivery,delivered'
        ]);

        $activeOrders = $request->user()->orders()->whereIn('status', $request->input('status'))->get();

        if ($activeOrders->isEmpty()) {
            return response([
                'status' => 'FAILED',
                'message' => 'NO_ACTIVE_ORDERS',
            ])->setStatusCode(404);
        }

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'ACTIVE_ORDERS_GOT_SUCCESSFULLY',
            'payload' => [
                'active_orders' => $activeOrders
            ]
        ];

    }  
}
