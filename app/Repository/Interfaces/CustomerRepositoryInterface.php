<?php

namespace App\Repository\Interfaces;

interface CustomerRepositoryInterface
{
    public function getStudentProfiles($customerId);
    function getCustomerNotifications($userId, $limit, $offset, $studentId);
}
