<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportsRequest;
use App\Reports\Invoice;
use App\Reports\TransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    private $reportsRequest;

    public function __construct(ReportsRequest $reportsRequest)
    {
        $this->reportsRequest = $reportsRequest;
    }

    public function printSingleInvoice(Request $request)
    {
        $this->reportsRequest->validatePrintSingleInvoice($request);

        $invoiceId = $request->invoice_id;
        $customerId = Auth::user()->id;

        $invoice = new Invoice($customerId, $invoiceId);
        return $invoice->pdf();
        
    }

    public function printTransactionHistory(Request $request)
    {
        $customerId = Auth::user()->id;
        $transactionHistory = new TransactionHistory($customerId);
        $transactionHistory->pdf();
    }

}
