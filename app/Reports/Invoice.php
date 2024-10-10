<?php

namespace App\Reports;

use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Reports\Support\AlquranClassesPdf;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;

class Invoice extends AbstractReport
{

    private $title = "Invoice";
    private $subTitle = "";
    private $query;

    use DecryptionTrait;
    public function __construct($customerId, $invoiceId)
    {
        $this->query = "call spCustomerTransactionHistorySingleInvoice($customerId, $invoiceId);";
        //$this->query = "call spCustomerTransactionHistorySingleInvoice(58, $invoiceId);";
    }

    public function pdf($destination = 'D')
    {
        $pdf = new AlquranClassesPdf('P', false, $this->title, '', '', true, $this->subTitle, false, true);
        /*
        $orientation = 'P', $pageHeaderRepeat = false, $reportTitle = 'Report', $fromDate = '', $toDate = '', 
        $isFooterRequired = true, $subTitle = '', $showHeader = true
        */
        $pdf->SetMargins(15, 10, 13);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->SetWidths(array(70));
        $pdf->SetAligns(array('L'));
        $pdf->SetY($pdf->GetY() - 7);
        $pdf->SetFont('Arial', 'B', 22);
        $invoiceHeading = array('Invoice');
        $pdf->AddRow($invoiceHeading, false);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(10);

        $customerName = '';
        $email = '';
        $studentName = '';
        $courseTitle = '';
        $transactions = array();
        $row = null;
        
        $transactions = DB::select($this->query);
        if(sizeof($transactions) > 0) {
            $pdf->SetWidths(array(35, 70));
            $pdf->SetAligns(array('L', 'L'));
            $row = $transactions[0];
            $invoiceDate = Carbon::parse($row->invoice_date)->format('F d, Y');
            $pdf->AddRow(array('Invoice number', $row->stripe_invoice_id), false);
            $pdf->Ln(3);
            $pdf->AddRow(array('Date of issue', $invoiceDate), false);
            $pdf->Ln(3);
            $pdf->AddRow(array('Date due', $invoiceDate), false);
            $pdf->Ln(10);

            $pdf->SetWidths(array(90, 70));
            $pdf->SetAligns(array('L', 'L'));
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->AddRow(array('AlQuranClasses b/o ITGenerations Inc', 'Bill to'), false);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(2);
            $pdf->AddRow(array('Lulworth Court', $this->decryptValue($row->customer_name)), false);
            $pdf->Ln(2);
            $pdf->AddRow(array('Mississauga l5n72', $this->decryptValue($row->email)), false);
            $pdf->Ln(2);
            $pdf->AddRow(array('Canada', ''), false);
            $pdf->Ln(2);
            $pdf->AddRow(array('+1 8663024897', ''), false);
            $pdf->Ln(2);
            $pdf->AddRow(array('support@alquranclasses.com', ''), false);
            $pdf->Ln(10);

            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetWidths(array(100));
            $pdf->AddRow(array('$'.$row->amount.' USD due '.$invoiceDate), false);
            $pdf->Ln();
            $pdf->AddRow(array('Payment Status: Paid'), false);
            $pdf->Ln(10);
            $pdf->SetWidths(array(90, 30, 30, 30));
            $pdf->SetAligns(array('L', 'C', 'C', 'R'));
            $pdf->SetFont('Arial', '', 12);
            $pdf->AddRow(array('Description', 'Qty', 'Unit price', 'Amount'), false);
            $pdf->SetLineWidth(0.5);
            $pdf->Line(16, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(2);
            $pdf->AddRow(array('AlQuranClasses Packages', 1, '$'.$row->amount, '$'.$row->amount), false);

            $invoiceFrom = Carbon::parse($row->invoice_date)->format('F d');
            $invoiceTo = Carbon::parse($row->invoice_date)->addMonth(1)->format('F d, Y');
            $invoiceDates = $invoiceFrom.' - '.$invoiceTo;
            $pdf->Ln(2);
            $pdf->AddRow(array($invoiceDates), false);

            $pdf->Ln(10);
            $pdf->SetWidths(array(90, 40, 50));
            $pdf->SetAligns(array('L', 'L', 'R'));
            $pdf->SetFont('Arial', '', 12);
            $this->drawLine($pdf);
            $pdf->AddRow(array('', 'Subtotal', '$'.$row->amount), false);
            $this->drawLine($pdf);
            $pdf->AddRow(array('', 'Total', '$'.$row->amount), false);
            $this->drawLine($pdf);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->AddRow(array('', 'Amount due', '$'.$row->amount.' USD'), false);
            $this->drawLine($pdf);

            return $pdf->Output($destination, 'invoice.pdf');

        } else {
            return response()->json([
                'success' => true,
                'message' => 'No data found',
                'data' => []
            ], 200);
        }

    }

    public function csv()
    {
       // ignore this method for now
    }

    private function drawLine($pdf)
    {
        $pdf->Ln(2);
        $pdf->Line(106, $pdf->GetY(), 194, $pdf->GetY());
        $pdf->Ln(2);
    }
}
