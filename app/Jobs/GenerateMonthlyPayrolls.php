<?php

namespace App\Jobs;

use App\Models\RegularClassRate;
use App\Models\TrialClassRate;
use App\Models\User;
use App\Traits\Dispatchable;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyPayrolls implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $user;
    protected $verificationUrl;

    public function __construct()
    {

    }

    /**
     * the below function should be called when the job is dispatched
     * and ideally, it should bring data from 
     */
    public function handle()
    {
        //add 14 days to the start of the month to get the 15th day of the month
        $fromDate = Carbon::now('UTC')->subMonthNoOverflow()->startOfMonth()->addDays(14)->format('Y-m-d');
        //13 with start of months gives 14th day of the month
        $toDate = Carbon::now('UTC')->startOfMonth()->addDays(13)->format('Y-m-d');
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $startDT = $fromDate.' 00:00:00';
        $endDT = $toDate.' 23:59:59';

        //single trial_class amount to get the last record and to multiply with total_trial_duration
        $trialClassRate = 0;
        $trialRate = TrialClassRate::orderBy('id', 'desc')->limit(1)->get();
        if($trialRate){
            $trialClassRate = $trialRate->rate;
        }

        //get teachers empty payrolls
        $payRolls = $this->getEmptyPayrollsArray($fromDate, $toDate, $createdAt);
        
        //trial classes count
        $trialClasses = $this->getTrialClassesCount($startDT, $endDT);

        //weekly classes count
        $weeklyClasses = $this->getWeeklyClassesCount($startDT, $endDT);
        
        //update payrolls
        $payRolls = $this->updatePayrollWithTrialAndWeeklyData($payRolls, $trialClasses, $weeklyClasses, $trialClassRate);

        //to insert data to db
        DB::table('payrolls')->insert($payRolls);

    }

    /**
     * get empty payrolls array with teacher ids
     * @param $fromDate
     * @param $toDate
     * @param $createdAt
     */
    private function getEmptyPayrollsArray($fromDate, $toDate, $createdAt) {
        $teachers = User::where('role', 'teacher')->orderBy('id')->get();
        $regularRates = RegularClassRate::orderBy('id')->get();

        $payRolls = array();
        foreach($teachers as $teacher) {
            $payRoll = array();
            $regularClassRate = $this->findTeacherRegularClassRate($teacher->id, $regularRates);
            $payRoll['teacher_id'] = $teacher->id;
            $payRoll['from_date'] = $fromDate;
            $payRoll['to_date'] = $toDate;
            $payRoll['regular_classes_count'] = 0;
            $payRoll['trial_classes_count'] = 0;
            $payRoll['total_regular_amount'] = 0;
            $payRoll['total_trial_amount'] = 0;
            $payRoll['total_regular_duration'] = 0;
            $payRoll['total_trial_duration'] = 0;
            $payRoll['net_to_pay'] = 0;
            $payRoll['regular_class_rate'] = $regularClassRate;
            $payRoll['created_at'] = $createdAt;
            $payRolls[] = $payRoll;
        }

        //to remove the regular_class_rate from the array, as we are not storing it in db
        foreach($payRolls as $key => $payRoll){
            unset($payRolls[$key]['regular_class_rate']);
        }
        return $payRolls;
    }

    /**
     * get trial classes count
     * @param $startDT
     * @param $endDT
     * @return array
     */
    private function getTrialClassesCount($startDT, $endDT) {
        $trialClasses = array();
       
        $trialData = DB::table('trial_classes')
        ->selectRaw('teacher_id, count(*) as trial_count')
        ->where('class_time', '>=', $startDT)
        ->where('class_time', '<=', $endDT)
        ->where('status', 'trial_successful')
        ->groupBy('teacher_id')
        ->orderBy('teacher_id', 'asc')
        ->get();
    
        foreach($trialData as $data){
            $trialClass = array(
                'teacher_id' => $data->teacher_id,
                'count' => $data->trial_count
            );
            $trialClasses[] = $trialClass;
        }

        return $trialClasses;
    }

    /**
     * get weekly classes count
     * @param $startDT
     * @param $endDT
     * @return array
     */
    private function getWeeklyClassesCount($startDT, $endDT) {
        $weeklyClasses = array();
       
        $weeklyData = DB::table('weekly_classes')
        ->selectRaw('teacher_id, count(*) as weekly_count')
        ->where('class_time', '>=', $startDT)
        ->where('class_time', '<=', $endDT)
        ->where('teacher_status', 'present')
        ->groupBy('teacher_id')
        ->orderBy('teacher_id', 'asc')
        ->get();
    
        foreach($weeklyData as $data){
            $weeklyClass = array(
                'teacher_id' => $data->teacher_id,
                'count' => $data->weekly_count
            );
            $weeklyClasses[] = $weeklyClass;
        }

        return $weeklyClasses;
    }

    /**
     * update payrolls with trial and weekly classes data
     * @param $payRolls
     * @param $trialClasses
     * @param $weeklyClasses
     * @return array
     */
    private function updatePayrollWithTrialAndWeeklyData($payRolls, $trialClasses, $weeklyClasses, $trialClassRate) {
        foreach($trialClasses as $trialClass){
            foreach($payRolls as $payRoll){
                if($payRoll['teacher_id'] == $trialClass['teacher_id']){
                    $payRoll['trial_classes_count'] = $trialClass['count'];
                    //to calculate in hours
                    $payRoll['total_trial_duration'] = $trialClass['count'] * 0.5;
                    break;
                }
            }
        }

        foreach($weeklyClasses as $weeklyClass){
            foreach($payRolls as $payRoll){
                if($payRoll['teacher_id'] == $weeklyClass['teacher_id']){
                    $payRoll['regular_classes_count'] = $weeklyClass['count'];
                    //to calculate in hours
                    $payRoll['total_regular_duration'] = $weeklyClass['count'] * 0.5;
                    break;
                }
            }
        }

        foreach($payRolls as $payRoll){
            $payRoll['total_regular_amount'] = $payRoll['total_regular_duration'] * $payRoll['regular_class_rate'];
            $payRoll['total_trial_amount'] = $payRoll['total_trial_duration'] * $trialClassRate;
            $payRoll['net_to_pay'] = $payRoll['total_regular_amount'] + $payRoll['total_trial_amount'];
        }

        return $payRolls;
    }

    private function findTeacherRegularClassRate($teacherId, $regularRates) {
        foreach($regularRates as $rate){
            if($rate->teacher_id == $teacherId){
                return $rate->rate;
            }
        }
        return 0;
    }
}
