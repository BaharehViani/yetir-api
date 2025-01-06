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
        // $courier = CourierInfo::where('user_id', $request->user()->id)->first();
        // $new_order->courier_id = $courier->id;
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

    public function updateStatus(Request $request) {
        $request->validate([
            'order_id' => 'required|ulid',
            'status' => 'required|string'
        ]);
        $order = Order::find($request->input('order_id'));
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

    public function showActiveOrders(Request $request) {
        $user = $request->user();
        $activeOrders = $user->orders()->whereIn('status', ['waiting_for_pickup', 'in_delivery'])->get();
        if ($activeOrders->isEmpty()) {
            return response([
                'status' => 'FAILED',
                'message' => 'NO_ACTIVE_ORDERS',
            ])->setStatusCode(404);
        }
        return [
            'status' => 'SUCCESSFUL',
            'message' => 'ACTIVE_ORDERS_FETCHED_SUCCESSFULLY',
            'payload' => [
                'active_orders' => $activeOrders
            ]
        ];
    } 

    public function showDeliveredOrders(Request $request) {
        $user = $request->user();
        $deliveredOrders = $user->orders()->where('status', 'delivered')->get();
        if ($deliveredOrders->isEmpty()) {
            return response([
                'status' => 'FAILED',
                'message' => 'NO_DELIVERED_ORDERS',
            ])->setStatusCode(404);
        }
        return [
            'status' => 'SUCCESSFUL',
            'message' => 'DELIVERED_ORDERS_FETCHED_SUCCESSFULLY',
            'payload' => [
                'delivered_orders' => $deliveredOrders
            ]
        ];
    }
}
