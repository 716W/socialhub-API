<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\MediaService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected MediaService $mediaService) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateProfileRequest $request)
    {
        // validated the request :-
        $data = $request->validated();
        $user = $request->user();

        // proccess the image by use MediaService :-
        if ($request->hasFile('avatar')) {
            if ($user->profile?->avatar) {
                $data['avatar'] = $this->mediaService->replace(
                    $request->file('avatar'),
                    $user->profile->avatar,
                    'avatars'
                );
            } else {
                $data['avatar'] = $this->mediaService->upload(
                    $request->file('avatar'),
                    'avatars'
                );
            }
        }

        // update :- 
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return $this->successResponse(
            $user->load('profile'),
            'Profile updated successfully',
            200
        );
    }
}
