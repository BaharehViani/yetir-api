<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoicesController extends Controller
{
    public function indexAll(Request $request) {
        return Invoice::where('user_id', $request->user()->id)->orderByRaw("FIELD(status, 'pending', 'paid')")->get();
    }

    public function indexPending(Request $request) {
        return Invoice::where('user_id', $request->user()->id)->where('status', 'pending')->get();
    }

    public function show(Request $request, $id) {
        return $request->user()->invoices()->find($id) ?: response([
            'status' => 'FAILED',
            'message' => 'INVOICE_NOT_FOUND'
        ])->setStatusCode(404);
    }
    
    public function update(Request $request, $id) {
        $request->validate([
            'status' => 'sometimes|in:paid,cancelled,refunded'
        ]);
        $invoice = $request->user()->invoices()->find($id);
        $invoice->status = 'paid';
        $invoice->save();
        return [
            'status' => 'SUCCESSFUL',
            'message' => 'INVOICE_UPDATED_SUCCESSFULLY',
            'payload' => $invoice
        ];
    }
}
