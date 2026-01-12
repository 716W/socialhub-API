<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\registerRequest;
use App\Services\RegisterService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __construct(protected RegisterService $registerService)
    {
    }
    
    /**
     * Register New User
     * 
     * Create a new user account with name, email, and password. Returns user details and access token.
     * 
     * @tag Authentication
     * @response 201 {"message": "User registered successfully", "user": {"id": 1, "name": "John Doe", "email": "john@example.com"}, "token": "1|abcdefghijklmnopqrstuvwxyz"}
     * @response 422 {"message": "Validation errors", "errors": {"email": ["The email has already been taken."]}}
     */
    public function __invoke(registerRequest $request)
    {
        // Register the user using the service
        $data = $this->registerService->register(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('password'),
        );

        return response()->json($data, 201);
    }
}
