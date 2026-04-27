<?php

declare(strict_types=1);

namespace App\docs;

/**
 * @OA\Info(
 *     title="bapidapi",
 *     version="1.0.0",
 *     description="RESTful parking-management API. All endpoints except auth/register, auth/login, auth/password/forgot, and auth/password/reset require a Bearer JWT token.",
 *     @OA\Contact(email="api@bapidapi.local")
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="Local development server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
 *         @OA\Property(property="message", type="string", example="Unauthenticated.")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         additionalProperties={
 *             "type": "array",
 *             "items": {"type": "string"}
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TokenResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="OK"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="access_token", type="string"),
 *         @OA\Property(property="token_type", type="string", example="bearer"),
 *         @OA\Property(property="expires_in", type="integer", example=900),
 *         @OA\Property(
 *             property="user",
 *             type="object",
 *             @OA\Property(property="id", type="string", format="uuid"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="email", type="string", format="email")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="DriverResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="employeeNumber", type="string", example="EMP-001"),
 *     @OA\Property(property="firstName", type="string", example="Jan"),
 *     @OA\Property(property="lastName", type="string", example="Kowalski"),
 *     @OA\Property(property="phone", type="string", example="+48123456789"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="language", type="string", example="pl"),
 *     @OA\Property(property="notes", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="VehicleResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="fleetNumber", type="string", example="FL-042"),
 *     @OA\Property(property="type", type="string", example="truck"),
 *     @OA\Property(property="licensePlate", type="string", example="WA12345"),
 *     @OA\Property(property="trailerPlate", type="string", nullable=true),
 *     @OA\Property(property="adr", type="boolean", example=false),
 *     @OA\Property(property="refrigerated", type="boolean", example=false),
 *     @OA\Property(property="heightCm", type="integer", nullable=true),
 *     @OA\Property(property="lengthCm", type="integer", nullable=true),
 *     @OA\Property(property="weightKg", type="integer", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ParkingLotResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="code", type="string", example="PL-WAW-01"),
 *     @OA\Property(property="name", type="string", example="Warsaw North"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(
 *         property="address",
 *         type="object",
 *         @OA\Property(property="street", type="string"),
 *         @OA\Property(property="postalCode", type="string"),
 *         @OA\Property(property="city", type="string"),
 *         @OA\Property(property="state", type="string", nullable=true),
 *         @OA\Property(property="countryCode", type="string", example="PL")
 *     ),
 *     @OA\Property(
 *         property="coordinates",
 *         type="object",
 *         @OA\Property(property="latitude", type="number", format="float"),
 *         @OA\Property(property="longitude", type="number", format="float")
 *     ),
 *     @OA\Property(property="securityLevel", type="integer", example=3),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="openingHours", type="object", nullable=true),
 *     @OA\Property(property="capacity", type="integer", example=200),
 *     @OA\Property(property="operatorName", type="string", nullable=true),
 *     @OA\Property(property="contactPhone", type="string", nullable=true),
 *     @OA\Property(property="checkInInstructions", type="string", nullable=true),
 *     @OA\Property(property="pricing", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ReservationResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="reservationNumber", type="string", example="RES-00001"),
 *     @OA\Property(property="parkingLotId", type="string", format="uuid"),
 *     @OA\Property(property="driverId", type="string", format="uuid"),
 *     @OA\Property(property="vehicleId", type="string", format="uuid"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="checkIn", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="checkOut", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="accessCode", type="string", nullable=true),
 *     @OA\Property(
 *         property="totalPrice",
 *         type="object",
 *         @OA\Property(property="amount", type="number", format="float", nullable=true),
 *         @OA\Property(property="currency", type="string", nullable=true)
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="cancelledAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="cancellationReason", type="string", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true)
 * )
 */
class OpenApi
{
}
