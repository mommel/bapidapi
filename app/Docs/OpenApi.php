<?php

declare(strict_types=1);

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'RESTful parking-management API. All endpoints except auth/register, auth/login, auth/password/forgot, and auth/password/reset require a Bearer JWT token.',
    title: 'bapidapi',
    contact: new OA\Contact(email: 'api@bapidapi.local')
)]
#[OA\Server(
    url: '/api/v1',
    description: 'Local development server'
)]
#[OA\SecurityScheme(
    securityScheme: 'BearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(
            property: 'error',
            type: 'object',
            properties: [
                new OA\Property(property: 'code', type: 'string', example: 'UNAUTHORIZED'),
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            )
        ),
    ]
)]
#[OA\Schema(
    schema: 'TokenResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'OK'),
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'access_token', type: 'string'),
                new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                new OA\Property(property: 'expires_in', type: 'integer', example: 900),
                new OA\Property(
                    property: 'user',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                    ]
                ),
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'DriverResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'employeeNumber', type: 'string', example: 'EMP-001'),
        new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
        new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
        new OA\Property(property: 'phone', type: 'string', example: '+48123456789'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'language', type: 'string', example: 'pl'),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'VehicleResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'fleetNumber', type: 'string', example: 'FL-042'),
        new OA\Property(property: 'type', type: 'string', example: 'truck'),
        new OA\Property(property: 'licensePlate', type: 'string', example: 'WA12345'),
        new OA\Property(property: 'trailerPlate', type: 'string', nullable: true),
        new OA\Property(property: 'adr', type: 'boolean', example: false),
        new OA\Property(property: 'refrigerated', type: 'boolean', example: false),
        new OA\Property(property: 'heightCm', type: 'integer', nullable: true),
        new OA\Property(property: 'lengthCm', type: 'integer', nullable: true),
        new OA\Property(property: 'weightKg', type: 'integer', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ParkingLotResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'code', type: 'string', example: 'PL-WAW-01'),
        new OA\Property(property: 'name', type: 'string', example: 'Warsaw North'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(
            property: 'address',
            type: 'object',
            properties: [
                new OA\Property(property: 'street', type: 'string'),
                new OA\Property(property: 'postalCode', type: 'string'),
                new OA\Property(property: 'city', type: 'string'),
                new OA\Property(property: 'state', type: 'string', nullable: true),
                new OA\Property(property: 'countryCode', type: 'string', example: 'PL'),
            ]
        ),
        new OA\Property(
            property: 'coordinates',
            type: 'object',
            properties: [
                new OA\Property(property: 'latitude', type: 'number', format: 'float'),
                new OA\Property(property: 'longitude', type: 'number', format: 'float'),
            ]
        ),
        new OA\Property(property: 'securityLevel', type: 'integer', example: 3),
        new OA\Property(property: 'amenities', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'openingHours', type: 'object', nullable: true),
        new OA\Property(property: 'capacity', type: 'integer', example: 200),
        new OA\Property(property: 'operatorName', type: 'string', nullable: true),
        new OA\Property(property: 'contactPhone', type: 'string', nullable: true),
        new OA\Property(property: 'checkInInstructions', type: 'string', nullable: true),
        new OA\Property(property: 'pricing', type: 'object', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ReservationResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'reservationNumber', type: 'string', example: 'RES-00001'),
        new OA\Property(property: 'parkingLotId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'driverId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'vehicleId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'checkIn', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'checkOut', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'accessCode', type: 'string', nullable: true),
        new OA\Property(
            property: 'totalPrice',
            type: 'object',
            properties: [
                new OA\Property(property: 'amount', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'currency', type: 'string', nullable: true),
            ]
        ),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'cancelledAt', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'cancellationReason', type: 'string', nullable: true),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
    ]
)]
class OpenApi
{
}
