<?php

namespace App\Models;

use App\Classes\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpParser\Builder;

class AvailabilitySlot extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;
    protected $fillable = ['availability_id', 'day_id', 'slot_id'];
    //protected $with = ['slot'];

    public function day(){
        return $this->belongsTo(Day::class, 'day_id');
    }

    public function availability()
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    public function slot(){
        return $this->belongsTo(Slot::class, 'slot_id');
    }

    public function routineClass(){
        return $this->hasOne(RoutineClass::class, 'slot_id');
    }

    public function reschedule_request()
    {
        return $this->hasOne(RescheduleRequest::class, 'reschedule_slot_id', 'id')->where('status', '<>', 'disapproved');
    }

    public function makeup_request()
    {
        return $this->hasOne(MakeupRequest::class, 'availability_slot_id', 'id')->where('status', '<>', 'disapproved');
    }

    public function trialClass() {
        return $this->hasOne(TrialClass::class)->where('status', StatusEnum::TrialRescheduled)
            ->orWhere('status', StatusEnum::TrialScheduled);
    }


    /**
     * Get slot id
     * @param Builder $query
     * @param array $ids
     * @return mixed
     */
    public function ScopeGetSlotId($query, array $ids=[]) {

        return $query->select('id')->where($ids);
    }


}
