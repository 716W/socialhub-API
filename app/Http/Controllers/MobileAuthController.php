<?php

namespace App\Http\Controllers;

use App\Services\VerificationService;
use Illuminate\Http\Request;

class MobileAuthController extends Controller
{
    public function __construct(protected VerificationService $verificationService)
    {
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        $result = $this->verificationService->verifyOtp($request->user(), $request->code);

        // check verification result
        if ($result === true) {
            return $this->successResponse('Email verified successfully.');
        }

        return $this->errorResponse($result , 400);
    }

    public function resend(Request $request)
    {
        $this->verificationService->sendOtp($request->user());
        return $this->successResponse('OTP has been resent to your email.');
    }
}
