<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __construct(protected VerificationService $verificationService){}
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $id, string $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->errorResponse('Invalid verification link', 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email already verified', 200);
        }

        $verified = $this->verificationService->verifyEmail($user);

        if (!$verified) {
            return $this->errorResponse('Unable to verify email', 400);
        }

        return $this->successResponse(null, 'Email verified successfully', 200);
    }
}
