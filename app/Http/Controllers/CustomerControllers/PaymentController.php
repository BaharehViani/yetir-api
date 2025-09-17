<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Models\Transaction;
use App\Models\Invoice as InvoiceModel;
use Illuminate\Http\Request;
use Shetabit\Multipay\Invoice as ShetabitInvoice;
use App\Http\Controllers\Controller;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

class PaymentController extends Controller
{
    public function payment(Request $request, $invoice_id) {

        $invoice = InvoiceModel::find($invoice_id);

        if (!$invoice) {
            return response([
                'status' => 'FAILED',
                'message' => 'Invoice not found'
            ])->setStatusCode(404);
        }

        $amount = $invoice->grand_total;

        return Payment::callbackUrl(route('payment.verify'))->purchase(
            (new ShetabitInvoice)->amount($amount), 
            function($driver, $transactionId) use ($invoice_id, $amount) {
                // Store transactionId in database.
                // We need the transactionId to verify payment in the future.
                $new_transaction = new Transaction();
                $new_transaction->invoice_id = $invoice_id;
                $new_transaction->transaction_id = $transactionId;
                $new_transaction->gateway = 'zarinpall';
                $new_transaction->amount = $amount;
                $new_transaction->status = 'pending';
                $new_transaction->ip = request()->ip();
                $new_transaction->save();   
            }
        )->pay()->render();
    }

    public function verify(Request $request) {

        $transaction_id = $request->input('Authority');
        $transaction = Transaction::where('transaction_id', $transaction_id)->first();

        if (!$transaction) {
            return response([
                'status' => 'FAILED',
                'message' => 'Transaction not found'
            ])->setStatusCode(404);
        }

        $invoice = $transaction->invoice;

        try {
	        $receipt = Payment::amount($transaction->amount)->transactionId($transaction_id)->verify();

            $transaction->status = 'completed';
            $transaction->ref_id = $receipt->getReferenceId();
            $transaction->paid_at = now();
            $transaction->save();

            $invoice->status = 'paid';
            $invoice->save();

            return response([
                'status' => 'SUCCESSFUL',
                'message' => 'Transaction was successful',
                'payload' => [
                    'invoice_id' => $invoice->id,
                    'ref_id' => $receipt->getReferenceId()
                ]
            ])->setStatusCode(200);

        } catch (InvalidPaymentException $exception) {
            /**
                when payment is not verified, it will throw an exception.
                We can catch the exception to handle invalid payments.
                getMessage method, returns a suitable message that can be used in user interface.
            **/

            $transaction->status = 'failed';
            $transaction->save();

            return response([
                'status' => 'FAILED',
                'message' => $exception->getMessage()
            ])->setStatusCode(422);
        }
    }
}
