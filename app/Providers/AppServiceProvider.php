<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Facebook\FacebookProvider;
use SocialiteProviders\Facebook\GoogleProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->app['events']->listen(SocialiteWasCalled::class, function (SocialiteWasCalled $socialiteWasCalled) {
            $socialiteWasCalled->extendSocialite('facebook', FacebookProvider::class);
            $socialiteWasCalled->extendSocialite('google', GoogleProvider::class);
        });
    }
}
