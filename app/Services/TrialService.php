<?php
namespace App\Services;

use App\Helpers\GeneralHelper;
use App\Models\AvailabilitySlot;
use App\Repository\Interfaces\TrialClassRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrialService
{
    private $trialClassRepository;

    public function __construct(
        TrialClassRepositoryInterface $trialClassRepository
    )
    {
        $this->trialClassRepository = $trialClassRepository;
    }

    /**
     * Create a trial class
     *
     * @param Request $request
     * @return mixed
     */
    public function createTrialClass($trialData)
    {
        // Get the availability slot
        $availabilitySlot = AvailabilitySlot::find($trialData['availability_slot_id']);

        // Get the day name
        $dayName = $availabilitySlot->day->day_name;

        // Generate the class time
        $classTime = $availabilitySlot->slot->slot;

        // Get today's date
        $today = Carbon::now();

        // Get the date of the next occurrence of the selected day
        $dateOfNextDay = new Carbon("next $dayName");

        // If the selected day is today, add 7 days to get the date of the next occurrence
        if ($today->dayOfWeekIso === $dateOfNextDay->dayOfWeekIso) {
            $dateOfNextDay->addWeek();
        }

        // Concatenate the date with the class time
        $classDateTime = $dateOfNextDay->toDateString() . ' ' . $classTime;

        // Convert the class time to UTC
        $classTimeUTC = GeneralHelper::convertTimeToUTCzone($classDateTime);

        $trialData['class_time'] = $classTimeUTC;
        $trialData['customer_id'] = Auth::id();

        // Create the trial class
        $trialClass = $this->trialClassRepository->create($trialData);

        if (isset($trialClass['error'])) {
            return $trialClass;
        }

        return $trialClass->refresh();
    }
}