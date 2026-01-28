<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerificationService
{
    public function verifyEmail(User $user): bool
    {
        // if the user has already verified their email .. return false 
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        // verify the user's email
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return true;
        }

        return false;
    }

    public function resendLink(User $user) : bool {

        if ($user->hasVerifiedEmail()) {
            return false ;
        }

        $user->sendEmailVerificationNotification();
        return true ;
    }
}