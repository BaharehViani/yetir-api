<?php

namespace App\Http\Controllers\CourierControllers;

use App\Models\Order;
use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    //
    public function create(Request $request) {
        $request->validate([
            'order_request_id' => 'required|ulid|exists:order_requests,id',
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
        $new_order->courier_id = $request->user()->courierinfo()->first()->id;
        $new_order->status = 'waiting_for_pickup';
        $new_order->save();
        
        $order_request->status = 'accepted';
        $order_request->save();

        return[
            'status' => 'SUCCESSFUL',
            'message' => 'REQUEST_ACCEPTED_AND_ORDER_CREATED_SUCCESSFULY'
        ];
    }

    public function update(Request $request, $id) {

        $request->validate([
            'status' => 'required|string'
        ]);

        $order = Order::where('courier_id', $request->user()->id)->find($id);

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
