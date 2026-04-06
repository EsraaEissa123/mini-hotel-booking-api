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
     * Create a new job instance.
     */
    public function __construct(
        public readonly Booking $booking
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Simulate sending an email or API request to a 3rd party
        $bookingCode = "BKG-" . str_pad((string)$this->booking->id, 6, "0", STR_PAD_LEFT);
        
        Log::info("Sending booking confirmation snippet...", [
            'booking_id' => $this->booking->id,
            'hotel_id'   => $this->booking->hotel_id,
            'user_email' => $this->booking->guest_email,
            'total_price'=> $this->booking->total_price,
            'code'       => $bookingCode,
        ]);

        // Simulated delay
        sleep(2);
        
        Log::info("Successfully sent confirmation to {$this->booking->guest_email}");
    }
}
