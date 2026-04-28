<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit/Models');

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
    $user = User::factory()->create([
        'email' => 'testuser-'.Str::random(8).'@example.com',
        'password' => bcrypt('password123'),
    ]);

    return auth('api')->login($user);
}
