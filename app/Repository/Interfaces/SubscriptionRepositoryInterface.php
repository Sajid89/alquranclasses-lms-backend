<?php

namespace App\Repository\Interfaces;

use App\Models\User;

interface SubscriptionRepositoryInterface
{
    public function hasActiveSubscription($studentId);
    public function enrollmentPlans($customerId);
    public function scheduleCancellationAtPeriodEnd($student_id, $student_course_id);
    public function previousClassSchedule($studentID, $studentNewSlots);
    public function updateSubscription($subscriptionId, $newPlanId);
    public function removeSubscription($subscriptionId);
}