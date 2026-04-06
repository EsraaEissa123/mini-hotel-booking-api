<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\RoomName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'name',
        'max_occupancy',
        'base_price',
        'total_rooms',
    ];

    protected function casts(): array
    {
        return [
            'name' => RoomName::class,
            'base_price' => 'decimal:2',
            'max_occupancy' => 'integer',
            'total_rooms' => 'integer',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function availableRooms($checkIn, $checkOut): int
    {
        $bookedRooms = $this->bookings()
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::CONFIRMED])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })
            ->sum('rooms_count');

        return max(0, $this->total_rooms - $bookedRooms);
    }
}
