<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\loginRequest;
use App\Services\LoginService;

class LoginController extends Controller
{
    public function __construct(protected LoginService $loginService)
    {
        
    }
    
    /**
     * Login User
     * 
     * Authenticate a user with email and password and receive an access token.
     * 
     * @tag Authentication
     * @response 200 {"message": "Login Successful", "data": {"user": {"id": 1, "name": "John Doe", "email": "john@example.com"}, "token": "1|abcdefghijklmnopqrstuvwxyz"}}
     * @response 401 {"message": "Invalid credentials"}
     */
    public function __invoke(loginRequest $request)
    {
        // call service & validate the coming data :-
        $result = $this->loginService->Login(
            $request->validated('email'),
            $request->validated('password')
        );

        // return response :-
        return response()->json([
            'message' => 'Login Successful',
            'data' => $result 
        ] , 200);

    }
}
