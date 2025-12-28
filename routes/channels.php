<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;




Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId; //pour les retraits d'attributions
});
