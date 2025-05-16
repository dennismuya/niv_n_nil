<?php

namespace App\Listeners;

use App\Events\stockMoved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class stockMovedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(stockMoved $event): void
    {
        //
    }
}
