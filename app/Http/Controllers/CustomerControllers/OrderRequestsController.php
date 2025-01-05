<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderRequestsController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->orderRequests()->orderBy('created_at', 'desc')->get();
    }

    public function show(Request $request, $id)
    {
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
        $random_price = rand(20, 500);
        $new_order_request->cost = $random_price;
        $new_order_request->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'REQUESTED_CREATED_SUCCESSFULLY'
        ];
    }
}
