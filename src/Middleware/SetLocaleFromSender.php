<?php

namespace Tarik02\LaravelTelegramExtra\Middleware;

use Closure;
use Illuminate\Foundation\Application;

use Tarik02\LaravelTelegram\{
    Contracts\Middleware,
    Request,
    Response
};

/**
 * Class SetLocaleFromSender
 * @package Tarik02\LaravelTelegramExtra\Middleware
 */
class SetLocaleFromSender implements Middleware
{
    /**
     * @var Application
     */
    protected Application $application;

    /**
     * @param Application $application
     * @return void
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|null
     */
    public function handle(Request $request, Closure $next): ?Response
    {
        if (
            (null !== $sender = $request->sender()) &&
            (null !== $locale = $sender->languageCode())
        ) {
            $this->application->setLocale($locale);
        }

        return $next($request);
    }
}
