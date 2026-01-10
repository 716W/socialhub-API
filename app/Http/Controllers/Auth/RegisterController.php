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
     * Handle the incoming request.
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
