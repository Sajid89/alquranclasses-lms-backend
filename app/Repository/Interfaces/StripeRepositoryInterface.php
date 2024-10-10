<?php

namespace App\Repository\Interfaces;

interface StripeRepositoryInterface
{
    public function createCustomer($name, $email);
    public function createCard($customerId, $token);
    public function createSubscription($user, $planId, $studentId, $couponCode);
    public function cancelSubscription($subscriptionId);
    public function upgradeDowngradeSubscription($subscriptionId, $newPlanId);
    public function cancelSubscriptionImmediately($subscriptionId);
}