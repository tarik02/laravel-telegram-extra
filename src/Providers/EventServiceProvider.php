<?php

namespace Tarik02\LaravelTelegramExtra\Providers;

use Event;
use Illuminate\Support\ServiceProvider;
use Tarik02\LaravelTelegramExtra\Listeners\AttachChatIdToMethod;

use Tarik02\LaravelTelegram\Events\{
    MethodCalling,
    WebhookMethodCalling
};

/**
 * Class EventServiceProvider
 * @package Tarik02\LaravelTelegramExtra\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->registerListeners();
    }

    /**
     * @return void
     */
    protected function registerListeners(): void
    {
        Event::listen(
            MethodCalling::class,
            AttachChatIdToMethod::class
        );
        Event::listen(
            WebhookMethodCalling::class,
            AttachChatIdToMethod::class
        );
    }
}
