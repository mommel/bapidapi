<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Driver;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\TestCase;

covers(Driver::class);

describe('Driver model', function () {
    describe('fillable attributes', function () {
        it('has the expected fillable fields', function () {
            $driver = new Driver();

            expect($driver->getFillable())->toBe([
                'employee_number',
                'first_name',
                'last_name',
                'phone',
                'email',
                'language',
                'notes',
            ]);
        });

        it('does not expose unexpected fillable fields', function () {
            $driver = new Driver();

            expect($driver->getFillable())
                ->not->toContain('id')
                ->not->toContain('created_at')
                ->not->toContain('updated_at');
        });
    });

    describe('traits', function () {
        it('uses the HasUuids trait', function () {
            expect(class_uses_recursive(Driver::class))
                ->toHaveKey(HasUuids::class);
        });

        it('uses the HasFactory trait', function () {
            expect(class_uses_recursive(Driver::class))
                ->toHaveKey(HasFactory::class);
        });
    });

    describe('table and primary key', function () {
        it('uses the correct database table name', function () {
            expect((new Driver())->getTable())->toBe('drivers');
        });

        it('uses id as the primary key', function () {
            expect((new Driver())->getKeyName())->toBe('id');
        });

        it('has an auto-incrementing key disabled (UUID)', function () {
            expect((new Driver())->getIncrementing())->toBeFalse();
        });

        it('has a string key type', function () {
            expect((new Driver())->getKeyType())->toBe('string');
        });
    });

    describe('mass assignment', function () {
        it('can be filled with valid attributes', function () {
            $driver = new Driver([
                'employee_number' => 'EMP-00001',
                'first_name'      => 'Jan',
                'last_name'       => 'Kowalski',
                'phone'           => '+48123456789',
                'email'           => 'jan.kowalski@example.com',
                'language'        => 'pl',
                'notes'           => 'Some note',
            ]);

            expect($driver->employee_number)->toBe('EMP-00001')
                ->and($driver->first_name)->toBe('Jan')
                ->and($driver->last_name)->toBe('Kowalski')
                ->and($driver->phone)->toBe('+48123456789')
                ->and($driver->email)->toBe('jan.kowalski@example.com')
                ->and($driver->language)->toBe('pl')
                ->and($driver->notes)->toBe('Some note');
        });

        it('ignores non-fillable attributes on mass assignment', function () {
            $driver = new Driver([
                'first_name' => 'Jan',
                'last_name'  => 'Kowalski',
                'id'         => 'should-be-ignored',
            ]);

            expect($driver->id)->toBeNull();
        });
    });

    describe('reservations() relationship', function () {
        it('returns a HasMany relation instance', function () {
            $driver = new Driver();

            expect($driver->reservations())->toBeInstanceOf(HasMany::class);
        });

        it('relates to the Reservation model', function () {
            $driver = new Driver();
            $relation = $driver->reservations();

            expect($relation->getRelated())->toBeInstanceOf(Reservation::class);
        });

        it('uses driver_id as the foreign key', function () {
            $driver = new Driver();
            $relation = $driver->reservations();

            expect($relation->getForeignKeyName())->toBe('driver_id');
        });

        it('uses id as the local key', function () {
            $driver = new Driver();
            $relation = $driver->reservations();

            expect($relation->getLocalKeyName())->toBe('id');
        });
    });
});
