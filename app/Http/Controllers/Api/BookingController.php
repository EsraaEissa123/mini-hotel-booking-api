<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateBookingDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $bookings = Booking::with(['hotel', 'roomType'])
            ->where('user_id', Auth::id())
            ->get();

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request): BookingResource
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id(); // Inject user ID

        $dto = CreateBookingDTO::fromArray($data);

        $booking = $this->bookingService->book($dto);
        $booking->load(['hotel', 'roomType']);

        return new BookingResource($booking);
    }

    public function show(Booking $booking): BookingResource
    {
        if ($booking->user_id !== Auth::id()) {
            abort(404);
        }

        $booking->load(['hotel', 'roomType']);
        return new BookingResource($booking);
    }

    public function cancel(Booking $booking): BookingResource
    {
        if ($booking->user_id !== Auth::id()) {
            abort(404);
        }

        $cancelledBooking = $this->bookingService->cancel($booking);
        $cancelledBooking->load(['hotel', 'roomType']);

        return new BookingResource($cancelledBooking);
    }
}
