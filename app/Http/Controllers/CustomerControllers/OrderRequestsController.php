<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderRequestsController extends Controller
{
    public function index(Request $request) {
        return $request->user()->orderRequests()->orderBy('created_at', 'desc')->get();
    }

    public function show(Request $request, $id) {
        return $request->user()->orderRequests()->find($id) ?: response([
            'status' => 'FAILED',
            'message' => 'ORDER_REQUEST_NOT_FOUND'
        ])->setStatusCode(404);
    }

    public function create(Request $request) {
        
        $request->validate([
            'type' => 'required|string',
            'description' => 'nullable|string',
            'pickup_location' => 'required|string',
            'dropoff_location' => 'required|string',
            'weight' => 'required',
        ]);
        $new_order_request = new OrderRequest;
        $new_order_request->type = $request->input('type');
        $new_order_request->description = $request->input('description');
        $new_order_request->pickup_location = $request->input('pickup_location');
        $new_order_request->dropoff_location = $request->input('dropoff_location');
        $new_order_request->weight = $request->input('weight');
        $new_order_request->user_id = $request->user()->id;
        $new_order_request->status = 'pending';
        $new_order_request->cost = rand(20, 100) * 1000;
        $new_order_request->code = (OrderRequest::max('code') ?? 999) + 1;
        $new_order_request->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'REQUESTED_CREATED_SUCCESSFULLY'
        ];
    }

    public function update(Request $request, $id) {

        $request->validate([
            'type' => 'sometimes|string',
            'description' => 'sometimes|string',
            'pickup_location' => 'sometimes|string',
            'dropoff_location' => 'sometimes|string',
            'weight' => 'sometimes',
            'status' => 'sometimes|string|in:declined',
        ]);
        $order_request = OrderRequest::where('user_id', $request->user()->id)->find($id);

        if (!$order_request || $order_request->status === 'declined') {
            return response([
                'status' => 'FAILED',
                'message' => 'ORDER_REQUEST_NOT_FOUND'
            ])->setStatusCode(404);
        }
        if ($order_request->status === 'pending') {
            if ($request->has('status')) {
                $order_request->status = $request->input('status');
            }
            if ($request->has('type')) {
                $order_request->type = $request->input('type');
            }
            if ($request->has('weight')) {
                $order_request->weight = $request->input('weight');
            }
            if($request->has('description')) {
                $order_request->description = $request->input('description');
            }
            if ($request->has('pickup_location')) {
                $order_request->pickup_location = $request->input('pickup_location');
            }
            if ($request->has('dropoff_location')) {
                $order_request->dropoff_location = $request->input('dropoff_location');
            }
            $order_request->updated_at = now();
            $order_request->save();
            return [
                'status' => 'SUCCESSFUL',
                'message' => 'REQUEST_UPDATED_SUCCESSFULLY'
            ];
        } else {
            return response([
                'status' => 'FAILED',
                'message' => 'REQUEST_CANNOT_BE_UPDATED'
            ])->setStatusCode(400);
        }

    }
}
