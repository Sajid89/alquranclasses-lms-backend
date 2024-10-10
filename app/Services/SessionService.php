<?php

namespace App\Services;

use App\Classes\Enums\CommonEnum;
use App\Models\DeviceSession;
use App\Repository\SessionRepository;
use App\Models\Session;

class SessionService
{
    protected $sessionRepository;

    /**
     * AvailabilityService constructor.
     * 
     * @param AvailabilitySlotRepositoryInterface $availabilitySlotRepository
     */
    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }
    
    /**
     * Create a new session
     * 
     * @param array $data
     * @return mixed
     */
    public function setUserSession($data)
    {
        // Get the count of current sessions for the user
        $sessionCount = DeviceSession::where('user_id', $data['user_id'])->count();

        // If the user has 5 or more sessions
        if ($sessionCount >= CommonEnum::SESSION_LIMIT) {
            // Get the oldest session for the user
            $oldestSession = DeviceSession::where('user_id', $data['user_id'])->oldest('login_time')->first();

            // Delete the oldest session
            if ($oldestSession) {
                $this->sessionRepository->delete($oldestSession->id);
            }
        }

        return $this->sessionRepository->create($data);
    }

    
}