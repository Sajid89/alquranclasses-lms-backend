<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    private $subscriptionService;
    private $subscriptionRequest;

    public function __construct(
        SubscriptionService $subscriptionService,
        SubscriptionRequest $subscriptionRequest
    )
    {
        $this->subscriptionService = $subscriptionService;
        $this->subscriptionRequest = $subscriptionRequest;
    }

    /**
     * Get the transaction history of a customer
     * 
     * @param Request $request
     * @return Response
     */
    public function transactionHistory(Request $request) {
        $user = Auth::user();
        if ($user) {
            $customerId = $user->id;
            $data = $this->subscriptionService->transactionHistory($customerId);
            return $this->success($data, 'Customer transaction history', 200);
        }
    }

    /**
     * Get the details of a single invoice
     * 
     * @param Request $request (customer_id, invoice_id)
     * @return Response
     */
    public function singleInvoiceDetails(Request $request) {
        $request = $this->subscriptionRequest->validateSingleInvoiceDetails($request);

        $customerId = Auth::user()->id;
        $invoiceId = $request->invoice_id;
        $data = $this->subscriptionService->singleInvoiceDetails($customerId, $invoiceId);

        return $this->success($data, 'Invoice details', 200);
    }
}
