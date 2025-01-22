<?php

namespace App\Http\Controllers\XUserControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return $request->user();
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response([
                'status' => 'FAILED',
                'message' => 'USER_NOT_FOUND'
            ])->setStatusCode(404);
        }

        $request->validate([
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'phone' => "sometimes|starts_with:0|size:11|unique:users,phone,$user->id",
            'password' => 'sometimes|string|min:8',
        ]);

        if ($request->has('first_name')) {
            $user->first_name = $request->input('first_name');
        }

        if ($request->has('last_name')) {
            $user->last_name = $request->input('last_name');
        }

        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->updated_at = now();
        $user->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'PROFILE_UPDATED_SUCCESSFULLY',
            'payload' => $user
        ];
    }
}    
