<?php

namespace App\Console\Commands;

use App\Classes\Enums\StatusEnum;
use App\Jobs\SendJobErrorMailJob;
use App\Models\WeeklyClass;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WeeklyClassesStatusUpdateAsPerAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WeeklyClassesStatusUpdateAsPerAttendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will update the status of classes as per their attendance of teacher & student';

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
        try
        {
            DB::transaction(function () {
                WeeklyClass::where('class_time', '<=', Carbon::now()->subMinutes(30))
                    ->whereIn('status',[StatusEnum::RESCHEDULED,StatusEnum::SCHEDULED,StatusEnum::MAKEUP])
                    ->where(function ($query) {
                        $query->where(function ($subQuery) {
                            $subQuery->where('teacher_presence', 0)
                            ->where('student_presence', 0);
                        })
                        ->orWhere('teacher_status', 'absent')
                        ->orWhere('teacher_status', 'present')
                        ->orWhere('teacher_status', 'scheduled');
                    })
                    ->update([
                        'status' => DB::raw('CASE
                           WHEN teacher_presence = 1 AND student_presence = 1 THEN "attended"
                           WHEN (teacher_presence = 0 AND student_presence = 0) OR teacher_status = "absent" THEN "absent"
                           WHEN teacher_status = "scheduled" THEN "absent"
                           WHEN teacher_status = "present" THEN "attended"
                           ELSE status
                        END')
                    ]);
            });
        }
        catch (Exception $e) {
            Log::debug('command:WeeklyClassesStatusUpdateAsPerAttendance failed Cron job error', $e->getMessage());
        }
    }
}
