<?php

namespace Tarik02\LaravelTelegramExtra\Middleware;

use Closure;
use Tarik02\Telegram\Methods\LeaveChat;

use Tarik02\LaravelTelegram\{
    Contracts\Middleware,
    Request,
    Response
};

/**
 * Class LeaveFromGroups
 * @package Tarik02\LaravelTelegramExtra\Middleware
 */
class LeaveFromGroups implements Middleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|null
     */
    public function handle(Request $request, Closure $next): ?Response
    {
        if (
            (null !== $chat = $request->chat()) &&
            $chat->type() !== 'private'
        ) {
            return Response::reply(
                LeaveChat::make()
                    ->withChatId($request->chat()->id())
            );
        }

        return $next($request);
    }
}
