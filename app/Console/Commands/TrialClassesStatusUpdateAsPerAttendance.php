<?php

namespace App\Console\Commands;

use App\Classes\Enums\StatusEnum;
use App\Helpers\GeneralHelper;
use App\Jobs\TrialMissedEmails;
use App\Models\TrialClass;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrialClassesStatusUpdateAsPerAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TrialClassesStatusUpdateAsPerAttendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will update the status of classes as per their attendance of teacher & student
     an also update trial request status for missed classes and send email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try{
            DB::transaction(function () {
                $trialClasses = TrialClass::where('class_time', '<=', Carbon::now()->subMinutes(30))
                    ->whereIn('status', [StatusEnum::TrialRescheduled, StatusEnum::TrialScheduled])
                    ->where(function ($query) {
                        $query->orWhere('teacher_status', 'absent')
                            ->orWhere('teacher_status', 'present')
                            ->orWhere('teacher_status', 'scheduled');
                    })
                    ->get();

                foreach ($trialClasses as $trialClass) {
                    $newStatus = $trialClass->status;
                    $newStudentStatus = $trialClass->student_status;
                    $newTeacherStatus = $trialClass->teacher_status;

                    $classDatetimeStdTz = GeneralHelper::convertTimeToUserTimezone($trialClass->class_time, $trialClass->student->timezone);
                    $classDatetimeTchrTz = GeneralHelper::convertTimeToUserTimezone($trialClass->class_time, $trialClass->teacher->timezone);
                    $emailData = [
                        'customer_name' => $trialClass->user->name,
                        'customer_email' => $trialClass->user->email,
                        'student_name' => $trialClass->student->name,
                        'class_datetime_std_tz' => Carbon::parse($classDatetimeStdTz)->format('Y-m-d H:i:s'),
                        'teacher_name' => $trialClass->teacher->name,
                        'teacher_email' => $trialClass->teacher->email,
                        'class_datetime_tchr_tz' => Carbon::parse($classDatetimeTchrTz)->format('Y-m-d H:i:s'),
                        'coordinator_name' => $trialClass->coordinator->name,
                        'coordinator_email' => $trialClass->coordinator->email
                    ];

                    if ($trialClass->teacher_presence == 0) 
                    {
                        $newStatus = StatusEnum::TrialMissed;
                        $newStudentStatus = StatusEnum::ABSENT;
                        $newTeacherStatus = StatusEnum::ABSENT;

                        $emailData['by_whom'] = 'teacher';
                        dispatch( new TrialMissedEmails($emailData));
                    } 
                    elseif ($trialClass->student_presence == 0) 
                    {
                        $newStatus = StatusEnum::TrialMissed;
                        $newStudentStatus = StatusEnum::ABSENT;
                        $newTeacherStatus = StatusEnum::ABSENT;

                        $emailData['by_whom'] = 'student';
                        dispatch( new TrialMissedEmails($emailData));
                    } 
                    elseif ($trialClass->teacher_status == 'absent')
                    {
                        $newStatus = StatusEnum::ABSENT;
                    } 
                    elseif ($trialClass->teacher_status == 'scheduled') 
                    {
                        $newStatus = StatusEnum::ABSENT;
                    } 
                    elseif ($trialClass->teacher_status == 'present') 
                    {
                        $newStatus = StatusEnum::ATTENDED;
                    }

                    // Update TrialClass
                    $trialClass->update([
                        'status' => $newStatus,
                        'student_status' => $newStudentStatus,
                        'teacher_status' => $newTeacherStatus
                    ]);
                }
            });
        }
        catch (Exception    $e) {
            Log::error('Command:TrialClassesStatusUpdateAsPerAttendance failed Cron job error', $e->getMessage());
        }
    }
}
