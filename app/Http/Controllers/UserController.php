<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResponse;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources;
use Illuminate\Support\Facades\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request)
    {
        // check the access permission
        Gate::authorize('viewAny',User::class);
        $users = $this->userService->getAllUsers();

        // we use the Trait :-
        return $this->successResponse(
            UserResource::collection($users),
            'All users retrieved successfully.'
        );
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        Gate::authorize('view', $user);

        return $this->successResponse(
            new UserResource($user),
        );
    }

    public function update(UserRequest $request , int $id) {

        $user = $this->userService->getUserById($id);

        // check the access permission
        Gate::authorize('update',$user);

        $validated = $request->validated();

        if (isset($validated['role']) && $request->user()->role !== 'admin') {
            unset($validated['role']);
        }

        return $this->successResponse(
            new UserResource($this->userService->updateUser($id,$validated)),
            'User updated successfully.'
        );
    }

    public function destroy($id) {
        $user = $this->userService->getUserById($id);
        Gate::authorize('delete', $user);
        
        $this->userService->deleteUser($id);
        return $this->successResponse(
            null ,
            'User deleted successfully.'
        );
    }
}
