<?php

namespace Tarik02\LaravelTelegramExtra\Providers;

use Illuminate\Support\ServiceProvider;
use Tarik02\LaravelTelegram\Request;
use Tarik02\Telegram\Methods\SendMessage;

use Tarik02\Telegram\Entities\{
    Chat,
    User
};

/**
 * Class MacroServiceProvider
 * @package Tarik02\LaravelTelegramExtra\Providers
 */
class MacroServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->registerRequestMacros();
    }

    /**
     * @return void
     */
    protected function registerRequestMacros(): void
    {
        Request::macro('sender', function (): ?User {
            /** @var Request $this */

            $update = $this->update();

            switch (true) {
                case null !== $message = $update->message():
                    return $message->from();

                case null !== $editedMessage = $update->editedMessage():
                    return $editedMessage->from();

                case null !== $inlineQuery = $update->inlineQuery():
                    return $inlineQuery->from();

                case null !== $chosenInlineResult = $update->chosenInlineResult():
                    return $chosenInlineResult->from();

                case null !== $callbackQuery = $update->callbackQuery():
                    return $callbackQuery->from();

                case null !== $shippingQuery = $update->shippingQuery():
                    return $shippingQuery->from();

                case null !== $preCheckoutQuery = $update->preCheckoutQuery():
                    return $preCheckoutQuery->from();

                case null !== $pollAnswer = $update->pollAnswer():
                    return $pollAnswer->user();

                default:
                    return null;
            }
        });

        Request::macro('chat', function (): ?Chat {
            /** @var Request $this */

            $update = $this->update();

            switch (true) {
                case null !== $message = $update->message():
                    return $message->chat();

                case null !== $editedMessage = $update->editedMessage():
                    return $editedMessage->chat();

                case null !== $channelPost = $update->channelPost():
                    return $channelPost->chat();

                case null !== $editedChannelPost = $update->editedChannelPost():
                    return $editedChannelPost->chat();

                case (
                    (null !== $callbackQuery = $update->callbackQuery())
                        && (null !== $message = $callbackQuery->message())
                ):
                    return $message->chat();

                default:
                    return null;
            }
        });

        Request::macro('isPrivateChat', function (): bool {
            /** @var Request $this */
            $chat = $this->chat();

            if ($chat === null) {
                return false;
            }

            return $chat->type() === 'private';
        });

        Request::macro('replyWithMessage', function (string $text): void {
            /** @var Request $this */
            $chat = $this->chat();

            if ($chat === null) {
                throw new \LogicException('The request does have a chat.');
            }

            $this->bot()->getApi()->sendMessage(
                SendMessage::make()
                    ->withChatId($chat->id())
                    ->withText($text)
            );
        });
    }
}
