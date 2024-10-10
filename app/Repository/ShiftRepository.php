<?php

namespace App\Repository;

use App\Models\Shift;

class ShiftRepository 
{
    private $model;

    public function __construct(Shift $model)
    {
        $this->model = $model;
    }

    public function getSlotsForShift($shiftFrom, $shiftTo)
    {
        // Check if $shiftFrom matches any of the db shifts' from times
        $matchingShifts = $this->model::where('from', $shiftFrom)->exists();
    
        if ($matchingShifts) {
            return $this->model::where('from', $shiftFrom)
                ->where('to', $shiftTo)
                ->with('shiftSlots')
                ->get()
                ->pluck('shiftSlots.*.slot_id')
                ->collapse();
        } else {
            return $this->model::where(function ($query) use ($shiftFrom, $shiftTo) {
                        $query->whereBetween('from', [$shiftFrom, $shiftTo])
                              ->orWhereBetween('to', [$shiftFrom, $shiftTo]);
                    })
                    ->with(['shiftSlots' => function ($query) use ($shiftFrom, $shiftTo) {
                        $query->whereHas('slot', function ($query) use ($shiftFrom, $shiftTo) {
                            $query->whereBetween('slot', [$shiftFrom, $shiftTo])
                                  ->orWhereRaw('? BETWEEN slot AND ADDTIME(slot, SEC_TO_TIME(duration * 60))', [$shiftFrom])
                                  ->orWhereRaw('? BETWEEN slot AND ADDTIME(slot, SEC_TO_TIME(duration * 60))', [$shiftTo]);
                        });
                    }])
                    ->get()
                    ->pluck('shiftSlots.*.slot_id')
                    ->collapse();
        }
    }

    public function getShiftSlots($shiftFrom, $shiftTo)
    {
        return $this->model::where('from', $shiftFrom)
            ->where('to', $shiftTo)
            ->with('shiftSlots')
            ->get()
            ->pluck('shiftSlots.*.slot_id')
            ->collapse();
    }
}