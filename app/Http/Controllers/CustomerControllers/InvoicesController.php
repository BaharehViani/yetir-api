<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    //
    public function index(Request $request) {
        return Invoice::where('user_id', $request->user()->id)->get();
    }
}
