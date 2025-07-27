<?php

use Illuminate\Support\Facades\Broadcast;



Broadcast::channel('posts', function ($user) {
    return $user !== null; // Only allow authenticated users
});
