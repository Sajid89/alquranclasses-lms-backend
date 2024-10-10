<?php

namespace App\Traits;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Lumen\Bus\PendingDispatch;

trait Dispatchable
{
    use InteractsWithQueue;

    /**
     * Dispatch the job with the given arguments.
     *
     * @param  mixed  ...$arguments
     * @return PendingDispatch
     */
    public static function dispatch(...$arguments)
    {
        return new PendingDispatch(new static(...$arguments));
    }

    /**
     * Determine if the job should be delayed.
     *
     * @return bool
     */
    public function shouldDelay()
    {
        return $this instanceof ShouldQueue;
    }
}
