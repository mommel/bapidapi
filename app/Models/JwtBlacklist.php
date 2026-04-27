<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * JWT Blacklist model for token revocation.
 *
 * @property int $id
 * @property string $jti
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class JwtBlacklist extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'jti',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Check if a token JTI is blacklisted.
     */
    public static function isBlacklisted(string $jti): bool
    {
        return self::where('jti', $jti)->exists();
    }

    /**
     * Delete expired entries to keep the table clean.
     */
    public static function purgeExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }
}
