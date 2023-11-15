<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
