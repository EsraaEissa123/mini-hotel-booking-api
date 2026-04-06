<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Booking $booking
    ) {}

    /**
     * Execute the job.
     *
     * In a real-world application, this would send an email via a
     * Mailable class or dispatch a notification to the guest.
     */
    public function handle(): void
    {
        $bookingCode = 'BKG-' . str_pad((string) $this->booking->id, 6, '0', STR_PAD_LEFT);

        Log::info('Booking confirmation dispatched', [
            'booking_id' => $this->booking->id,
            'hotel_id'   => $this->booking->hotel_id,
            'user_email' => $this->booking->guest_email,
            'total_price' => $this->booking->total_price,
            'code'       => $bookingCode,
        ]);

        // TODO: Replace with actual Mail::to($this->booking->guest_email)->send(new BookingConfirmationMail($this->booking));
    }
}
