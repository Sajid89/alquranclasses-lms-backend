<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'slot_id'
    ];

    //protected $with = ['shifts'];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    /**
     * Scope a query to only include slots for a given shift.
     * @param $query
     * @param $shiftId
     */
    public function scopeGetShiftSlots($query, $shiftId)
    {
        $query->where('shift_id', $shiftId);
    }
}
