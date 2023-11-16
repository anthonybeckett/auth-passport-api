<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response->assertStatus(201);
        $response->assertSee('message');
        $response->assertSee('token');
    }
}
