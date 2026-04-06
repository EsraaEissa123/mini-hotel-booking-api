<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateBookingDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\Contracts\BookingServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly BookingServiceInterface $bookingService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $bookings = Booking::with(['hotel', 'roomType'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request): BookingResource
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $dto = CreateBookingDTO::fromArray($data);

        $booking = $this->bookingService->book($dto);
        $booking->load(['hotel', 'roomType']);

        return (new BookingResource($booking))
            ->response()
            ->setStatusCode(201)
            ->original;
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        $booking->load(['hotel', 'roomType']);
        return new BookingResource($booking);
    }

    public function cancel(Booking $booking): BookingResource
    {
        $this->authorize('cancel', $booking);

        $cancelledBooking = $this->bookingService->cancel($booking);
        $cancelledBooking->load(['hotel', 'roomType']);

        return new BookingResource($cancelledBooking);
    }
}
