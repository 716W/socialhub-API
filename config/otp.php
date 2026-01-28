<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for OTP (One-Time Password)
    | verification used in mobile authentication.
    |
    */

    'length' => env('OTP_LENGTH', 6),

    'expiration' => env('OTP_EXPIRATION', 10), // minutes

    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),

];
