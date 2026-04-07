<?php

declare(strict_types=1);

use App\Models\User;

describe('POST /api/v1/auth/register', function () {
    it('registers a user and returns a token', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => fake()->password(8, 32),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['access_token', 'token_type', 'expires_in', 'user'],
            ])
            ->assertJson(['success' => true]);
    });

    it('returns 422 when email is missing', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => fake()->name(),
            'password' => fake()->password(8, 32),
        ]);

        $response->assertStatus(422);
    });

    it('returns 422 when email is already taken', function () {
        $existing = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => fake()->name(),
            'email' => $existing->email,
            'password' => fake()->password(8, 32),
        ]);

        $response->assertStatus(422);
    });
});

describe('POST /api/v1/auth/login', function () {
    it('returns a token for valid credentials', function () {
        $password = fake()->password(8, 32);
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['access_token', 'token_type', 'expires_in'],
            ]);
    });

    it('returns 401 for invalid credentials', function () {
        $user = User::factory()->create([
            'password' => bcrypt('correctpassword'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    });

    it('returns 422 when email is missing', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'password' => fake()->password(8, 32),
        ]);

        $response->assertStatus(422);
    });
});

describe('GET /api/v1/auth/me', function () {
    it('returns the authenticated user', function () {
        $token = authToken();

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    });

    it('returns 401 without a token', function () {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    });
});

describe('POST /api/v1/auth/logout', function () {
    it('invalidates the token', function () {
        $token = authToken();

        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200);
    });

    it('returns 401 without a token', function () {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    });
});
