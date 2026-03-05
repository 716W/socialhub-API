<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Services\MediaService;
use App\Services\ProfileServie;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected ProfileServie $profileService) {}
    /**
     * Handle the incoming request.
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');
        return $this->successResponse(
            new UserResource($user),
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
