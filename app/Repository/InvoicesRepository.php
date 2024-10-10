<?php

namespace App\Repository;

use App\Classes\Enums\UserTypesEnum;
use App\Models\Invoice;
use App\Models\User;
use App\Repository\Interfaces\InvoicesRepositoryInterface;
use App\Traits\DecryptionTrait;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicesRepository implements InvoicesRepositoryInterface
{
    private $model;
    use DecryptionTrait;
    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    /**
     * Get the transaction history of a customer
     * 
     * @param int $customerId
     * @return array
     */
    public function getTransactionHistory($customerId) 
    {
        $query = "CALL spCustomerTransactionHistory($customerId);";
        $data = [];

        $resultSet = DB::select($query);
        foreach ($resultSet as $result) {
            $data[] = array(
                'invoice_id' => $result->id,
                'status' => 'Paid',
                'billing_period' => 'Monthly',
                'student_name' => $this->decryptValue($result->name),
                'profile_photo_url' => $result->profile_photo_url,
                'course_title' => $result->title,
                'amount' => $result->amount, 
                'created_at' => $result->created_at, 
                'invoice_date' => $result->invoice_date, 
                'gender' => $result->gender, 
                'subscription_status' => $result->subscription_status, 
                'timezone' => $result->timezone, 
                'vacation_mode' => $result->vacation_mode, 
                'deleted_at' => $result->deleted_at, 
                'planID' => $result->planID, 
                'course_level' => $result->course_level, 
                'is_custom' => $result->is_custom, 
                'description' => $result->description, 
                'customer_name' => $this->decryptValue($result->customer_name), 
                'email' => $this->decryptValue($result->email),
                'profile_photo_path' => $result->profile_photo_path 
            );
        }
       
        return $data;
    }

    public function getSingleInvoiceDetails($customerId, $invoiceId) {
        $query = "CALL spCustomerTransactionHistorySingleInvoice($customerId, $invoiceId);";
        $data = [];
        
        $resultSet = DB::select($query);
        foreach ($resultSet as $result) {
            $data[] = array(
                'invoice_id' => $invoiceId,
                'stripe_invoice_id' => $result->stripe_invoice_id,
                'status' => 'Paid',
                'billing_period' => 'Monthly',
                'student_name' => $this->decryptValue($result->name),
                'profile_photo_url' => $result->profile_photo_url,
                'course_title' => $result->title,
                'amount' => $result->amount, 
                'created_at' => $result->created_at, 
                'invoice_date' => $result->invoice_date, 
                'gender' => $result->gender, 
                'subscription_status' => $result->subscription_status, 
                'timezone' => $result->timezone, 
                'vacation_mode' => $result->vacation_mode, 
                'deleted_at' => $result->deleted_at, 
                'planID' => $result->planID, 
                'course_level' => $result->course_level, 
                'is_custom' => $result->is_custom, 
                'description' => $result->description, 
                'customer_name' => $this->decryptValue($result->customer_name), 
                'email' => $this->decryptValue($result->email),
                'profile_photo_path' => $result->profile_photo_path 
            );
        }
       
        return $data;
    }
}