<?php

namespace App\Jobs;
use App\Entity\QueueThrottleExceptionsLimitor;

use App\Services\VSDKWebhookCalculationService;
use App\Services\ZoomWebhookCalculationService;
use App\Traits\QueTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MarkClassStatusJob  extends QueueThrottleExceptionsLimitor implements  ShouldQueue
{
    use QueTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $details;
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            return (new VSDKWebhookCalculationService)->MarkClassStatus($this->details);
        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
    }
}
