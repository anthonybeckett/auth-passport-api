<?php

namespace Tests\Feature\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use RefreshDatabase;
    public function testUserCredentialsFailValidation(): void
    {
        $response = $this->post('/api/register', [
            'name' => '',
            'email' => 'test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors('name');
        $response->assertSessionHasErrors('password');
    }

    public function testUserCanRegister(): void
    {
        $response = $this->withHeaders([
            'accept' => 'application/json'
        ])->post('/api/register', [
            'name' => 'test',
            'email' => 'user@test.com',
            'password' => 'test',
            'password_confirmation' => 'test',
        ]);

        $response->assertStatus(201);
        $response->assertContent('{"status":true,"message":"User created successfully"}');
    }

    public function testValidationFailsWhenUserDoesNotExist(): void
    {
        $response = $this->post('/api/login', [
            'email' => 'nouser@user.com',
            'password' => 'test',
        ]);

        $response->assertStatus(401);
        $response->assertContent('{"status":false,"message":"Invalid login details"}');
    }

    public function testValidationFailsWhenMissingParameters(): void
    {
        $response = $this->post('/api/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors('password');
    }

    public function testUserCanLoginSuccessfully(): void
    {
        Passport::$hashesClientSecrets = false;

        $this->artisan(
            'passport:client',
            ['--name' => config('app.name'), '--personal' => null]
        )->assertSuccessful();

        $response = $this->createAndLoginUser();

        $response->assertStatus(201);
        $response->assertSee('message');
        $response->assertSee('token');
    }

    public function testUserCannotAccessProfileInformationWithoutToken(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
        $response->assertSee("Unauthenticated");
    }

    public function testUserCanAccessProfileInformationWithToken(): void
    {
        Passport::$hashesClientSecrets = false;

        $this->artisan(
            'passport:client',
            ['--name' => config('app.name'), '--personal' => null]
        )->assertSuccessful();

        $response = $this->createAndLoginUser();

        $result = json_decode($response->getContent());

        $actualResponse = $this->getJson('/api/profile', [
            "Authorization" => "Bearer " . $result->token
        ]);

        $actualResponse->assertStatus(201);
        $actualResponse->assertSee("test@test.com");
    }

    public function testUserCannotAccessLogoutRouteWithoutToken(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
        $response->assertSee("Unauthenticated");
    }

    public function testUserCanLogoutUsingToken(): void
    {
        Passport::$hashesClientSecrets = false;

        $this->artisan(
            'passport:client',
            ['--name' => config('app.name'), '--personal' => null]
        )->assertSuccessful();

        $response = $this->createAndLoginUser();

        $result = json_decode($response->getContent());

        $actualResponse = $this->post(uri: '/api/logout', headers: [
            "Authorization" => "Bearer " . $result->token
        ]);

        $actualResponse->assertStatus(201);
        $actualResponse->assertSee("Logged out successfully");
    }

    private function createAndLoginUser(): TestResponse
    {
        $this->post('/api/register', [
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->post('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        return $response;
    }
}
