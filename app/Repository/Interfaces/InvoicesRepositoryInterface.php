<?php

namespace App\Repository\Interfaces;

interface InvoicesRepositoryInterface
{

    public function getTransactionHistory($customerId);
    public function getSingleInvoiceDetails($customerId, $invoiceId);
}
