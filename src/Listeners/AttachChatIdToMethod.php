<?php

namespace Tarik02\LaravelTelegramExtra\Listeners;

use Illuminate\Contracts\Container\Container;
use Tarik02\Telegram\Methods\HasRequiredChatId;

use Tarik02\LaravelTelegram\{
    Events\MethodCalling,
    Request
};

/**
 * Class AttachChatIdToMethod
 * @package Tarik02\LaravelTelegramExtra\Listeners
 */
class AttachChatIdToMethod
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @param Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param MethodCalling $event
     * @return void
     */
    public function handle(MethodCalling $event): void
    {
        $method = $event->method;

        if (! ($method instanceof HasRequiredChatId) || $method->hasValidChatId()) {
            return;
        }

        if (! $this->container->bound('telegram.request')) {
            return;
        }

        /** @var Request $request */
        $request = $this->container->get('telegram.request');
        if ($request === null) {
            return;
        }

        $chat = $request->chat();
        if ($chat === null) {
            return;
        }

        $event->method = $method
            ->withChatId($chat->id());
    }
}
