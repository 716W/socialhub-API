<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Logout User
     * 
     * Revoke the current access token and log the user out.
     * 
     * @tag Authentication
     * @response 200 {"message": "Logged out successfully"}
     * @unauthenticated
     */
    public function __invoke(Request $request)
    {
        // Logout the user
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
