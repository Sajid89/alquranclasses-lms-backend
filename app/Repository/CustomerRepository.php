<?php

namespace App\Repository;

use App\Models\Notification;
use App\Models\User;
use App\Repository\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Get customer profile
     *
     * @param int $customerId
     * @return User
     */
    public function getStudentProfiles($customerId) {
        $users = User::with(['students.subscription', 'students.studentCourses.course'])
            ->where('id', $customerId)
            ->get();

        return $users;
    }

    /**
     * Get customer notifications
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param int $studentId
     * @return array
     */
    public function getCustomerNotifications(
        $userId, $limit, 
        $offset, $studentId
    ) 
    {
        $totalNotifications = $this->getTotalNotifications($userId, $studentId);

        $notifications = $this->getNotifications($userId, $limit, $offset, $studentId);
                    
        $notifications->each(function ($notification) {
            //$notification->student_name  = $notification->student->name;
            $notification->makeHidden('student');
        });

        return [
            'total' => $totalNotifications,
            'notifications' => $notifications,
        ];
    }

    public function getNotifications($userId, $limit, $offset, $studentId = null) {
        return Notification::select('student_id', 'type', 'message', 'read', 'created_at')
        ->where('user_id', $userId)
        ->when($studentId, function ($query, $studentId) {
            return $query->where('student_id', $studentId);
        })
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->offset($offset)
        ->get();
    }

    public function getTotalNotifications($userId, $studentId = null) {
        return Notification::where('user_id', $userId)
            ->when($studentId, function ($query, $studentId) {
                return $query->where('student_id', $studentId);
            })
            ->count();

    }

    public function getAllTeachersNotifications($userIds, $limit, $offset) {
        return Notification::select('student_id', 'type', 'message', 'read', 'created_at')
        ->whereIn('user_id', $userIds)
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->offset($offset)
        ->get();
    }

    public function getAllTeachersTotalNotifications($userIds) {
        return Notification::whereIn('user_id', $userIds)
            ->count();

    }

    public function getTeachersNotifications(
        $userIds, $limit, 
        $offset
    ) 
    {
        $totalNotifications = $this->getAllTeachersTotalNotifications($userIds);

        $notifications = $this->getAllTeachersNotifications($userIds, $limit, $offset);
                    
        $notifications->each(function ($notification) {
            //$notification->student_name  = $notification->student->name;
            $notification->makeHidden('student');
        });

        return [
            'total' => $totalNotifications,
            'notifications' => $notifications,
        ];
    }

}
