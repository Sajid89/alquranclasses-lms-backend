<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Facebook\FacebookProvider;
use SocialiteProviders\Google\GoogleProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ],
    ];

    public function boot()
    {
        $this->app['events']->listen(SocialiteWasCalled::class, function (SocialiteWasCalled $socialiteWasCalled) {
            $socialiteWasCalled->extendSocialite('facebook', FacebookProvider::class);
            $socialiteWasCalled->extendSocialite('google', GoogleProvider::class);
        });
    }
}
