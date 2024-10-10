<?php

namespace App\Reports;

use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Reports\Support\AlquranClassesPdf;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;

class TransactionHistory extends AbstractReport
{

    private $title = "Alquran Classes";
    private $subTitle = "Transaction History Report";
    private $query;
    use DecryptionTrait;

    public function __construct($customerId)
    {
        $this->query = "call spCustomerTransactionHistory($customerId);";
    }

    public function pdf($destination = 'D')
    {
        $pdf = new AlquranClassesPdf('P', false, $this->title, '', '', true, $this->subTitle);
        $pdf->SetMargins(15, 10, 13);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->SetWidths(array(20, 70));
        $pdf->SetAligns(array('L', 'L'));

        $customerName = '';
        $email = '';
        $studentName = '';
        $courseTitle = '';
        $transactions = DB::select($this->query);
        $size = sizeof($transactions);

        if($size > 0) {
            $customerName = $this->decryptValue($transactions[0]->customer_name);
            $email = $this->decryptValue($transactions[0]->email);
            // $studentName = $transactions[0]->name;
            // $courseTitle = $transactions[0]->title;

            $data1 = array('Customer Name : ', $customerName);
            $data2 = array('Email : ', $email);
            // $data3 = array('Student Name : ', $studentName);
            // $data4 = array('Course Title : ', $courseTitle);
            $data5 = array('Status : ', 'Paid');
            $data6 = array('Billing Period : ', 'Monthly');
    
            $pdf->AddRow($data1, false);
            $pdf->AddRow($data2, false);
            // $pdf->AddRow($data3, false);
            // $pdf->AddRow($data4, false);
            $pdf->AddRow($data5, false);
            $pdf->AddRow($data6, false);
    
            $pdf->SetWidths(array(15, 25, 45, 60, 19, 19));
            $pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'C'));
            $pdf->AddTableHeader(array('S.No', 'Date', 'Student Name', 'Course', 'Invoice ID', 'Amount'));
            $pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'R'));
    
            foreach ($transactions as $idx => $transaction) {
                $dateTime = date_create($transaction->invoice_date);
                $formatedDate = date_format($dateTime, 'M-d-Y');

                $pdf->AddRow(array(
                    $idx + 1,
                    $formatedDate,
                    $this->decryptValue($transaction->name),
                    $transaction->title,
                    $transaction->id,
                    $transaction->amount
                ));
            }
        }

        return $pdf->Output($destination, 'transaction_history.pdf');
    }

    public function csv()
    {
       // ignore this method for now
    }
}
