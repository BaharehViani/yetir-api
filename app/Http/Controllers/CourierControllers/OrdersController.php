<?php

namespace App\Http\Controllers\CourierControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    //
    public function create(Request $request) {
        $request->validate([
            'order_request_id' => 'required|ulid|exists:requests,id',
        ]);

        $order_request = OrderRequest::find($request->input('order_request_id'));
        if (!$order_request) {
            return response([
                'status' => 'FAILED',
                'message' => 'REQUEST_NOT_FOUND'
            ])->setStatusCode(404);
        }

        if ($order_request->status !== 'pending') {
            return response([
                'status' => 'FAILED',
                'message' => 'REQUEST_CANNOT_BE_ACCEPTED'
            ])->setStatusCode(400);
        }

        $new_order = new Order;
        $new_order->order_request_id = $order_request->id;
        $courier = CourierInfo::where('user_id', $request->user()->id)->first();
        $new_order->courier_id = $courier->id;
        $new_order->status = 'waiting_for_pickup';
        $new_order->save();
        
        $order_request->status = 'accepted';
        $order_request->save();

        return response([
            'status' => 'FAILED',
            'message' => 'REQUEST_ACCEPTED_AND_ORDER_CREATED_SUCCESSFULY'
        ])->setStatusCode(200);
    }

    public function updateStatus(Request $request) {
        $request->validate([
            'order_id' => 'required|ulid',
            'status' => 'required'
        ]);
        $order = Order::find($request->order_id);
        if (!$order) {
            return response([
                'status' => 'FAILED',
                'message' => 'ORDER_NOT_FOUND'
            ])->setStatusCode(404);
        }
        $order->status = $request->input('status');
        $order->save();
        return [
            'status' => 'SUCCESSFUL',
            'message' => 'ORDER_STATUS_UPDATED_SUCCESSFULLY',
        ];
            
    }
}
