<?php

namespace Tarik02\LaravelTelegramExtra\Providers;

use Illuminate\Support\ServiceProvider;
use Tarik02\LaravelTelegram\Telegram as TelegramBase;
use Tarik02\LaravelTelegramExtra\Telegram;

/**
 * Class TelegramServiceProvider
 * @package Tarik02\LaravelTelegramExtra\Providers
 */
class TelegramServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(TelegramBase::class, Telegram::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../resources/app/Telegram/Dispatcher.php.stub' => \base_path('app/Telegram/Dispatcher.php'),
            __DIR__ . '/../../resources/app/Telegram/Kernel.php.stub' => \base_path('app/Telegram/Kernel.php'),
        ], 'app');

        $this->publishes([
            __DIR__ . '/../../resources/config/telegram.php.stub' => \config_path('telegram.php'),
        ], 'config');
    }
}
