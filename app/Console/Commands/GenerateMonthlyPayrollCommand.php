<?php
namespace App\Console\Commands;

use App\Jobs\GenerateMonthlyPayrolls;
use App\Jobs\SendJobErrorMailJob;
use App\Models\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyPayrollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:generate';    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is supposed to calculate and save the monthly payroll of all teachers. 
    it will run every month on 5th day';    
    
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
     * @return int
     */
    public function handle()
    {
        try {
            dispatch(new GenerateMonthlyPayrolls());
        } catch(Exception $e){
            Log::error($e->getMessage());
        }
    }
}