<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('screen-share', function ($user) {
    return true; // Adjust this to fit your authorization logic
});
