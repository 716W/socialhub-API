<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;

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

    // -------------------------------------------------
    // Generate Code and Send it to App (Mobile) :-
    // -------------------------------------------------

    public function sendOtp(User $user): void
    {
        $length = config('otp.length', 6);
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_repeat('9', $length);
        
        // genrate a 6-digit OTP code
        $code = (string) rand($min, $max);

        // store the hashed code in time-limited cache
        $user->update([
            'otp_code' => hash('sha256', $code),
            'otp_expires_at' => Carbon::now()->addMinutes(config('otp.expiration', 10)),
            'otp_attempts' => 0, // reset attempts when new OTP is sent
        ]);

        // send email with the OTP code
        Mail::to($user->email)->send(new OtpMail($code));
    }

    public function verifyOtp(User $user, string $inputCode)
    {
        // check if code is expired
        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return 'Code expired';
        }

        // check if max attempts exceeded
        $maxAttempts = config('otp.max_attempts', 5);
        if ($user->otp_attempts >= $maxAttempts) {
            return 'Maximum attempts exceeded. Please request a new code.';
        }

        // increment attempts
        $user->increment('otp_attempts');

        // check code matches (compare hashed values)
        if (!hash_equals($user->otp_code, hash('sha256', $inputCode))) {
            return 'Invalid code';
        }

        // mark email as verified
        // update verification fields for the user
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // clear otp fields
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
        ]);

        return true;
    }
}