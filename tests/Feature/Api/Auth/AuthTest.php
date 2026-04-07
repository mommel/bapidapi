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
    it('returns the authenticated user with structured data', function () {
        $token = authToken();

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'email'],
            ])
            ->assertJsonMissing(['password']);
    });

    it('returns 401 without a token', function () {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    });
});

describe('POST /api/v1/auth/logout', function () {
    it('invalidates the token and blacklists it', function () {
        $token = authToken();

        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200);

        // Token should now be blacklisted in the database
        $this->assertDatabaseCount('jwt_blacklists', 1);
    });

    it('returns 401 without a token', function () {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    });
});

describe('POST /api/v1/auth/password/forgot', function () {
    it('returns 200 for existing email (does not reveal existence)', function () {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    });

    it('returns 422 when email is missing', function () {
        $response = $this->postJson('/api/v1/auth/password/forgot', []);

        $response->assertStatus(422);
    });
});

describe('POST /api/v1/auth/password/reset', function () {
    it('returns 422 when token is missing', function () {
        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => fake()->safeEmail(),
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
    });

    it('returns 400 for invalid reset token', function () {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => 'invalid-token-value',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400);
    });
});
