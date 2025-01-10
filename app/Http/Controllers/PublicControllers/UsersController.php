<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|starts_with:0|size:11|unique:users,phone',
            'password' => 'required|string|min:8',
            'role' => 'required|in:customer,courier',
            'national_code' => 'required|unique:users,national_code|size:10',
        ]);

        $newUser = new User;
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        $newUser->phone = $request->input('phone');
        $newUser->password = Hash::make($request->input('password'));
        $newUser->role = $request->input('role');
        $newUser->national_code = $request->input('national_code');
        $newUser->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'USER_CREATED_SUCCESSFULLY'
        ];
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'phone' => 'required|starts_with:0|size:11',
            'password' => 'required|string|min:8'
        ]);

        $user = User::where('phone', $request->input('phone'))->first();

        if (!$user) {
            return response([
                'status' => 'FAILED',
                'message' => 'WRONG_CREDENTIALS'
            ])->setStatusCode(401);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            return response([
                'status' => 'FAILED',
                'message' => 'WRONG_CREDENTIALS'
            ])->setStatusCode(401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'AUTHENTICATION_SUCCESSFUL',
            'payload' => [
                'token' => $token
            ]
        ];
    }
}
