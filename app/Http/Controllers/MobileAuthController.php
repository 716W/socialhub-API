<?php

namespace App\Http\Controllers;

use App\Http\Requests\MobileVerifyRequest;
use App\Services\VerificationService;
use Illuminate\Http\Request;

class MobileAuthController extends Controller
{
    public function __construct(protected VerificationService $verificationService)
    {
    }

    public function verify(MobileVerifyRequest $request)
    {
        $result = $this->verificationService->verifyOtp($request->user(), $request->validated('code'));

        if ($result === true) {
            return $this->successResponse(null, 'Email verified successfully.');
        }

        return $this->errorResponse($result, 400);
    }

    public function resend(Request $request)
    {
        $this->verificationService->sendOtp($request->user());
        return $this->successResponse(null, 'OTP has been resent to your email.');
    }
}

