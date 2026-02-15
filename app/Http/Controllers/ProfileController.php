<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Services\MediaService;
use App\Services\ProfileServie;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected ProfileServie $profileService) {}
    /**
     * Handle the incoming request.
     */
    public function show(Request $request)
    {
        $user = $this->profileService->getProfile($request->user());
        return $this->successResponse(
            new ProfileResource($user->profile),
        );
    }
    public function update(UpdateProfileRequest $request)
    {
        $profile = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new ProfileResource($profile),
            'Profile updated successfully'
        );
    }
}
