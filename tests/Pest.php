<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Create a user and return a JWT token for authenticated requests.
 */
function authToken(): string
{
    $user = \App\Models\User::factory()->create([
        'email' => 'testuser-' . \Illuminate\Support\Str::random(8) . '@example.com',
        'password' => bcrypt('password123'),
    ]);

    return auth('api')->login($user);
}
