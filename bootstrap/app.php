<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        return app()->basePath('public/'.$path);
    }
}

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(App\Services\TwilioService::class, function ($app) {
    return new App\Services\TwilioService();
});

Sentry\init(['dsn' => env('SENTRY_LARAVEL_DSN')]);

$app->singleton('sentry', function () {
    return \Sentry\SentrySdk::getCurrentHub();
});


/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');

$app->configure('database');

$app->configure('auth');

$app->configure('custom');

$app->configure('mail');

$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);

$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);

class_alias(Laravel\Socialite\Facades\Socialite::class, 'Socialite');

$app->configure('sentry');

$app->configure('pusher');

$app->configure('broadcasting');
/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

 $app->middleware([
     App\Http\Middleware\CorsMiddleware::class,
     App\Http\Middleware\RateLimitMiddleware::class,
 ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

$app->routeMiddleware([
    'check.token' => App\Http\Middleware\CheckToken::class,
    'country.restriction' => \App\Http\Middleware\CountryRestrictionMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Illuminate\Database\DatabaseServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\RepositoryServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);

// Socialite service providers
$app->register(Laravel\Socialite\SocialiteServiceProvider::class);
$app->register(\SocialiteProviders\Manager\ServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

// Sentry service providers
//$app->register(Sentry\Laravel\ServiceProvider::class);

// Register Service Providers
$app->register(Illuminate\Broadcasting\BroadcastServiceProvider::class);
// register the auth driver for broadcasting
$app->register(App\Providers\BroadcastServiceProvider::class);


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
