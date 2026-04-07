<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\DriverRepositoryInterface;
use App\Repositories\Eloquent\EloquentDriverRepository;
use App\Repositories\Eloquent\EloquentParkingLotRepository;
use App\Repositories\Eloquent\EloquentReservationRepository;
use App\Repositories\Eloquent\EloquentVehicleRepository;
use App\Repositories\ParkingLotRepositoryInterface;
use App\Repositories\ReservationRepositoryInterface;
use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        DriverRepositoryInterface::class => EloquentDriverRepository::class,
        VehicleRepositoryInterface::class => EloquentVehicleRepository::class,
        ParkingLotRepositoryInterface::class => EloquentParkingLotRepository::class,
        ReservationRepositoryInterface::class => EloquentReservationRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
