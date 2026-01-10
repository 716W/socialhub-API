<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\loginRequest;
use App\Services\LoginService;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */

    public function __construct(protected LoginService $loginService)
    {
        
    }
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
