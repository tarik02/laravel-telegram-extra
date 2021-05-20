<?php

namespace Tarik02\LaravelTelegramExtra;

use Tarik02\LaravelTelegram\Telegram as TelegramBase;
use Tarik02\Telegram\Contracts\CallsMethods;
use Tarik02\Telegram\Methods\Method;
use Tarik02\Telegram\Traits\CallsMethods as CallsMethodsTrait;

/**
 * Class Telegram
 * @package Tarik02\LaravelTelegramExtra
 */
class Telegram extends TelegramBase implements CallsMethods
{
    use CallsMethodsTrait;

    /**
     * @param Method $method
     * @return mixed
     */
    public function call(Method $method)
    {
        return $this->app['telegram.bot']->getApi()->call($method);
    }
}
