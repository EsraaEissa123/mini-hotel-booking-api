# Mini Hotel Booking API

A RESTful Hotel Booking API built with Laravel 11, PHP 8.2+, and MySQL.

## Project Description
This API provides a streamlined experience for managing hotel bookings, specifically designed to demonstrate robust business logic, overbooking prevention, and dynamic pricing strategies.

## Development Plan

- **Phase 0:** Initial documentation and planning.
- **Phase 1:** Laravel project setup and Sanctum configuration.
- **Phase 2:** Database layer with migrations and critical indexes.
- **Phase 3:** Implementation of Enums, Models, and Relationships.
- **Phase 4:** Authentication (Registration, Login, Logout) with Sanctum.
- **Phase 5:** CRUD endpoints for Hotels and Room Types.
- **Phase 6:** Data Transfer Objects (DTOs) for clear layer communication.
- **Phase 7:** Pricing Service for complex nightly calculations.
- **Phase 8:** Availability Search with occupancy and price calculation.
- **Phase 9:** Booking Service with pessimistic locking for overbooking prevention.
- **Phase 10:** Booking management (view, create, cancel).
- **Phase 11:** Global API exception handling.
- **Phase 12:** Database seeders for realistic data.
- **Phase 13:** Detailed unit and feature testing.
- **Phase 14:** Final documentation update.

## Overbooking Prevention
To ensure data consistency and prevent overbooking in high-concurrency environments, we implement **Pessimistic Locking**. When a booking request is initiated, the application:
1. Starts a database transaction.
2. Uses `lockForUpdate()` on the specific `room_type` row. This ensures any concurrent requests for the same room type must wait until the current transaction is finished.
3. Calculates real-time availability *inside* the lock.
4. Completes the booking or throws an error.
5. Commits the transaction, releasing the lock.

## Pricing Logic
Our system uses a dynamic nightly pricing strategy:
- **Base Price**: Defined at the `RoomType` level.
- **Weekend Surcharge**: +20% for nights falling on Friday or Saturday.
- **Long-Stay Discount**: -10% on the *total sum* for bookings of 5 nights or more.
- Calculated per room and then multiplied by the number of requested rooms.

## API Endpoints (Placeholder)
| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| POST | `/api/auth/register` | Register new user | No |
| POST | `/api/auth/login` | Login and get token | No |
| GET | `/api/hotels` | List hotels | No |
| GET | `/api/availability` | Search for available rooms | No |
| POST | `/api/bookings` | Create a booking | Yes |

## Setup Instructions (Placeholder)
*Coming soon...*

## Assumptions Made
1. **Dynamic Room Pricing**: Pricing is fixed per room type but calculated dynamically based on dates.
2. **Global Timezone**: All dates are handled in UTC.
3. **Availability**: Availability is calculated on-the-fly to ensure accuracy.
4. **User Scope**: Users can only see and manage their own bookings.
