<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    public function availabilitySlots() {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function shiftSlots() {
        return $this->hasMany(ShiftSlot::class);
    }

    /**
     * Get Slots by shift ids
     * @param $query
     * @param $slotIds
     */
    public function scopeGetSlotsByShift($query, $slotIds)
    {
        $query->whereIn('id', $slotIds);
    }

    public function scopeGetSlotByTime($query, $time)
    {
        $query->where('slot', $time);
    }
}
