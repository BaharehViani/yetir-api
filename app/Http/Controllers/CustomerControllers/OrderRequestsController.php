<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
