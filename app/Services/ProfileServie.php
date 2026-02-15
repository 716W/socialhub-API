<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProfileServie
{
    public function __construct(protected  MediaService $mediaService) {}

    public function GetProfile(User $user)
    {
        return $user->load('profile');
    }
    public function UpdateProfile(User $user , array $data)
    {
        return DB::transaction(function () use ($user , $data)
        {
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile){

                // process the avatar upload and get the path
                if ($user->profile && $user->profile->avatar) {
                    $data['avatar'] = $this->mediaService->replace(
                        $data['avatar'],
                        $user->profile->avatar,
                        'avatars'
                    );
                } else {
                    $data['avatar'] = $this->mediaService->upload(
                        $data['avatar'],
                        'avatars'
                    );
                }
            }

            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
            return $profile;
        });
    }
}