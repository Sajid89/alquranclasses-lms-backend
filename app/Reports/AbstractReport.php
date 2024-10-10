<?php

namespace App\Reports;
use App\Reports\IReport;

abstract class AbstractReport implements IReport {
    protected $ADDRESS = 'Company Address';
    protected $NTN = '00000-00000';
    
    public function changeDateToMysql($date) {
        $dateArr = explode('-', $date);
        //11-09-2023
        $dateMysql = $dateArr[2].'-'.$dateArr[1].'-'.$dateArr[0];
        
        return $dateMysql;
    }
    
    public function getMysqlDateWithStartTime($date) {
        return $this->changeDateToMysql($date).' 00:00:00';
    }

    public function getMysqlDateWithEndTime($date) {
        return $this->changeDateToMysql($date).' 23:59:59';
    }

    public function getCleanData($data) {
        $data = str_replace("{", '', $data);
        $data = str_replace("}", '', $data);
        $data = str_replace(",", ', ', $data);
        $data = str_replace('"', '', $data);
        
        $data = str_replace("model_type:", ' ', $data);
        $data = str_replace("App\\", '', $data);
        $data = str_replace("\Models\\", '', $data);
        
        return $data;
    }
    
    public function explodeAndFindCleanData($data) {
        $dataArr = explode(",", $data);
        $newArr = array();
        $size = sizeof($dataArr);
        for($m = 0; $m < $size; $m++) {
            $tr = trim($dataArr[$m]);
            if($tr != '') {
                $newArr[] = $tr;
            }
        }

        $newData = implode(", ", $newArr);

        return $newData;

    }

    public function signature($pdf, $title, $x1, $y1, $x2, $y2) {
        $pdf->Cell(50, 5, $title, 0, 0, 'L', FALSE);
        //Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
        $pdf->Line($x1, $y1, $x2, $y2);
    }
    
}
