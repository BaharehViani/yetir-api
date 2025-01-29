<?php

namespace App\Http\Controllers\CourierControllers;

use App\Models\CourierInfo;
use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourierController extends Controller
{
    public function create(Request $request) {
        $request->validate([
            'photo_url' => 'nullable|string',
        ]);
        $new_courier = new CourierInfo;
        $new_courier->user_id = $request->user()->id;
        $new_courier->photo_url = $request->input('photo_url') ?? null;
        $new_courier->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'COURIER_INFO_CREATED_SUCCESSFULLY'
        ];
    }

    public function index(Request $request) {
        $courier = $request->user()->courierinfo()->first();
        $vehicle = $request->user()->vehicles()->get();
        return ['courier' => $courier, 'vehicle' => $vehicle];
    }
}    
