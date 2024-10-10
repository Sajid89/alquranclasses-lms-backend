<?php

namespace App\Providers;

use App\Repository\Interfaces\StripeRepositoryInterface;
use App\Repository\StripeRepository;
use App\Repository\Interfaces\AvailabilityRepositoryInterface;
use App\Repository\Interfaces\AvailabilitySlotRepositoryInterface;
use App\Repository\Interfaces\EloquentRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Repository\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repository\BaseRepository;
use App\Repository\AvailabilityRepository;
use App\Repository\AvailabilitySlotRepository;
use App\Repository\CancelationReasonsRepository;
use App\Repository\NotToCancelReasonsRepository;
use App\Repository\Interfaces\InvoicesRepositoryInterface;
use App\Repository\Interfaces\RoutineClassRepositoryInterface;
use App\Repository\Interfaces\TeacherRepositoryInterface;
use App\Repository\InvoicesRepository;
use App\Repository\RoutineClassRepository;
use App\Repository\TeacherRepository;
use App\Repository\Interfaces\CustomerRepositoryInterface;
use App\Repository\CustomerRepository;
use App\Repository\Interfaces\SubscriptionRepositoryInterface;
use App\Repository\Interfaces\TrialClassRepositoryInterface;
use App\Repository\SubscriptionRepository;
use App\Repository\TrialClassRepository;
use App\Repository\Interfaces\CancelationReasonsRepositoryInterface;
use App\Repository\Interfaces\NotToCancelReasonsRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        $this->app->bind(AvailabilityRepositoryInterface::class, AvailabilityRepository::class);
        $this->app->bind(AvailabilitySlotRepositoryInterface::class, AvailabilitySlotRepository::class);

        $this->app->bind(TeacherRepositoryInterface::class, TeacherRepository::class);
        $this->app->bind(StripeRepositoryInterface::class, StripeRepository::class);
        $this->app->bind(RoutineClassRepositoryInterface::class, RoutineClassRepository::class);
        $this->app->bind(InvoicesRepositoryInterface::class, InvoicesRepository::class);

        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(TrialClassRepositoryInterface::class, TrialClassRepository::class);
        
        $this->app->bind(CancelationReasonsRepositoryInterface::class, CancelationReasonsRepository::class);
        $this->app->bind(NotToCancelReasonsRepositoryInterface::class, NotToCancelReasonsRepository::class);

    }
}
