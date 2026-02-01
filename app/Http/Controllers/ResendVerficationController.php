<?php

namespace App\Http\Controllers;

use App\Services\VerificationService;
use Illuminate\Http\Request;

class ResendVerficationController extends Controller
{
    public function __construct(protected VerificationService $verificationService)
    {
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // use the verification service to resend the verification link
        $sent = $this->verificationService->resendLink($request->user());

        if (!$sent) {
            return $this->successResponse(null, 'Email already verified', 200);
        }

        return $this->successResponse(null, 'Verification link sent', 202);
    }
}
