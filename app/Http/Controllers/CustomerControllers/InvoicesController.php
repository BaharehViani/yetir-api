<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoicesController extends Controller
{
    //
    public function index(Request $request) {
        return Invoice::where('user_id', $request->user()->id)->where('status', 'pending')->get();
    }
    
}
