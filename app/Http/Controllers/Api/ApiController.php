<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiControllerLoginRequest;
use App\Http\Requests\Api\ApiControllerRegisterRequest;
use App\Models\User;
use http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function register(ApiControllerRegisterRequest $request): JsonResponse
    {
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        return response()->json([
            "status" => true,
            "message" => "User created successfully"
        ])->setStatusCode(201);
    }

    public function login(ApiControllerLoginRequest $request): JsonResponse
    {
        $loginDetails = [
            "email" => $request->email,
            "password" => $request->password,
        ];

        if(Auth::attempt($loginDetails)){
            $user = Auth::user();

            $token = $user->createToken("passportToken")->accessToken;

            return response()->json([
                "status" => true,
                "message" => "Logged in successfully",
                "token" => $token
            ])->setStatusCode(201);
        }

        return response()->json([
            "status" => false,
            "message" => "Invalid login details"
        ])->setStatusCode(401);

    }

    public function profile(): JsonResponse
    {
        return response()->json([
            "status" => true,
            "message" => "Profile Information",
            "data" => Auth::user()
        ])->setStatusCode(201);
    }

    public function logout(Request $request): JsonResponse
    {
        auth()->user()->token()->revoke();

        return response()->json([
            "status" => true,
            "message" => "Logged out successfully"
        ])->setStatusCode(201);
    }
}
