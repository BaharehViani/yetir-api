<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    //
    public function index(Request $request) {

        $request->validate([
            'status' => 'nullable|string',
        ]);

        $validStatuses = ['waiting_for_pickup', 'in_delivery', 'delivered'];

        $statusArray = $request->input('status') ? explode(',', $request->input('status')) : null;

       if ($statusArray) {
            foreach ($statusArray as $status) {
                if (!in_array($status, $validStatuses)) {
                    return response([
                        'status' => 'FAILED',
                        'message' => 'INVALID_STATUS_VALUE',
                    ])->setStatusCode(422);
                }
            }
        }
        
        $query = $request->user()->customerorders();
    
        if ($statusArray) {
            $query->whereIn('orders.status', $statusArray);
        }
    
        $orders = $query->orderBy('created_at', 'desc')->get();
    
        if ($orders->isEmpty()) {
            return response([
                'status' => 'FAILED',
                'message' => 'NO_ACTIVE_ORDERS',
            ])->setStatusCode(404);
        }
    
        return $orders;
    }  
}
