<?php

namespace App\Http\Controllers\CourierControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    //
    public function addVehicle(Request $request) {

        $request->validate([
            'type' => 'required|string|',
            'plate_number' => 'required|string||unique:vehicles,plate_number',
            'description' => 'nullable|string',
            'maximum_capacity' => 'required',
        ]);

        $new_vehicle = new Vehicle();
        $new_vehicle->user_id = $request->user()->id;
        $new_vehicle->type = $request->input('type');
        $new_vehicle->plate_number = $request->input('plate_number');
        $new_vehicle->description = $request->input('description');
        $new_vehicle->maximum_capacity = $request->input('maximum_capacity');
        $new_vehicle->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'VEHICLE_CREATED_SUCCESSFULLY'
        ];
    }
}
