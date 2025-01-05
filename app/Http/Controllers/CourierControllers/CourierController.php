<?php

namespace App\Http\Controllers\CourierControllers;

use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourierController extends Controller
{
    //
    public function create(Request $request) {
        $request->validate([
            'photo_url' => 'nullable|string',
        ]);
        $new_courier = new CourierInfo;
        $new_courier->user_id = $request->user()->id;
        $new_courier->photo_url = $request->photo_url ?? null;
        $new_courier->save();
    }
}
