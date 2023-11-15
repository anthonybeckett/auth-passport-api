<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiControllerRegisterRequest;
use App\Models\User;
use http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function login(Request $request): void
    {
        //
    }

    public function profile(): void
    {
        //
    }

    public function logout(Request $request): void
    {
        //
    }
}
