<?php

namespace App\Http\Controllers\CourierControllers;

use App\Models\Order;
use App\Models\Invoice;
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
        
        if($request->user()->courierinfo()->first()->orders()) {
            $activeOrder = $request->user()->courierinfo()->first()->orders()->whereIn('status', ['waiting_for_pickup', 'in_delivery'])->first();
            if ($activeOrder) {
                return response([
                    'status' => 'FAILED',
                    'message' => 'COURIER_HAS_ACTIVE_ORDER'
                ])->setStatusCode(400);
            }
        }

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
        $new_order->code = (Order::max('code') ?? 999) + 1;
        $new_order->save();
        
        $order_request->status = 'accepted';
        $order_request->save();

        $new_invoice = new Invoice;
        $new_invoice->order_id = $new_order->id;
        $new_invoice->user_id = $order_request->user_id;
        $new_invoice->total = $order_request->cost;
        $new_invoice->tax = $order_request->cost / 10;
        $new_invoice->grand_total = $new_invoice->total + $new_invoice->tax;
        $new_invoice->status = 'pending';
        $new_invoice->save();

        return[
            'status' => 'SUCCESSFUL',
            'message' => 'REQUEST_ACCEPTED_ORDER_CREATED_INVOICE_CREATED_SUCCESSFULY',
            'payload' => [
                'order' => $new_order
            ]
        ];
    }

    public function update(Request $request, $id) {

        $request->validate([
            'status' => 'required|string|in:waiting_for_pickup,in_delivery,delivered,canceled',
        ]);  

        $order = Order::where('courier_id', $request->user()->courierinfo()->first()->id)->find($id);
        
        if (!$order || $order->status === 'canceled') {
            return response([
                'status' => 'FAILED',
                'message' => 'ORDER_NOT_FOUND'
            ])->setStatusCode(404);
        }

        if ($request->input('status') === 'canceled') {
            $pre_orderRequest = $order->orderRequest;
            //$pre_orderRequest = $order->orderRequest()->first();
            $pre_orderRequest->status = 'pending';
            $pre_orderRequest->updated_at = now();
            $pre_orderRequest->save();
            $order->canceled_at = now();
        }

        $order->status = $request->input('status');
        $order->updated_at = now();
        $order->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'ORDER_UPDATED_SUCCESSFULLY',
        ];      
    }

    public function index(Request $request) {

        $request->validate([
            'status' => 'nullable|string', 
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
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

        $query = $request->user()->courierorders();

        if ($statusArray) {
            $query->whereIn('status', $statusArray);
        }
    
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate || $endDate) {
            $query->whereBetween('orders.updated_at', [$startDate, $endDate]);
        }

        $orders = $query->orderBy('updated_at', 'desc')->get();
    
        if ($orders->isEmpty()) {
            return response([
                'status' => 'FAILED',
                'message' => 'NO_ACTIVE_ORDERS',
            ])->setStatusCode(404);
        }
        return $orders;
    }

    public function show(Request $request, $id) {
        return $request->user()->courierorders()->find($id) ?: response([
            'status' => 'FAILED',
            'message' => 'ORDER_NOT_FOUND'
        ])->setStatusCode(404);
    }  
    
    public function getActiveOrder(Request $request) {
        return $this->index($request)->first() ?: null;
    } 
}
