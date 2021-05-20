<?php

namespace Tarik02\LaravelTelegramExtra\Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

use Tarik02\LaravelTelegram\{
    Contracts\Middleware,
    Exceptions\ResponseException,
    Request,
    Response
};
use Tarik02\Telegram\Methods\{
    AnswerCallbackQuery,
    SendMessage
};

/**
 * Class SomethingWentWrong
 * @package Tarik02\LaravelTelegramExtra\Middleware
 */
class SomethingWentWrong implements Middleware
{
    /**
     * @var ExceptionHandler
     */
    protected ExceptionHandler $exceptionHandler;

    /**
     * @var ConfigRepository
     */
    protected ConfigRepository $configRepository;

    /**
     * @param ExceptionHandler $exceptionHandler
     * @param ConfigRepository $configRepository
     * @return void
     */
    public function __construct(ExceptionHandler $exceptionHandler, ConfigRepository $configRepository)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->configRepository = $configRepository;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|null
     */
    public function handle(Request $request, Closure $next): ?Response
    {
        try {
            return $next($request);
        } catch (ResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->exceptionHandler->report($exception);

            if ($this->shouldReplyWithSomethingWentWrong($request, $exception)) {
                return $this->replyWithSomethingWentWrong($request, $exception);
            }

            return null;
        }
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @return Response|null
     */
    protected function replyWithSomethingWentWrong(Request $request, Throwable $exception): ?Response
    {
        $update = $request->update();

        switch (true) {
            case null !== $message = $update->message():
                return Response::reply(
                    SendMessage::make()
                        ->withChatId($message->chat()->id())
                        ->withText($this->getSomethingWentWrongMessage($request, $exception))
                        ->withParseMode('MarkdownV2')
                );

            case null !== $callbackQuery = $update->callbackQuery():
                return Response::reply(
                    AnswerCallbackQuery::make()
                        ->withCallbackQueryId($callbackQuery->id())
                        ->withText($this->getSomethingWentWrongMessage($request, $exception, true))
                );

            default:
                return null;
        }
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return bool
     */
    protected function shouldReplyWithSomethingWentWrong(Request $request, Throwable $exception): bool
    {
        return $request->isPrivateChat();
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @param bool $short
     * @return string
     */
    protected function getSomethingWentWrongMessage(Request $request, Throwable $exception, bool $short = false): string
    {
        if (! $this->configRepository->get('app.debug', false)) {
            return $this->getFallbackSomethingWentWrongMessage($request, $exception, $short);
        }

        if ($short) {
            return $exception->getMessage();
        }

        $output = new BufferedOutput();

        $this->exceptionHandler->renderForConsole($output, $exception);

        $message = $output->fetch();
        $message = \preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $message);
        $message = \str_replace('\\', '\\\\', $message);

        return '```' . $message . '```';
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @param bool $short
     * @return string
     */
    protected function getFallbackSomethingWentWrongMessage(
        Request $request,
        Throwable $exception,
        bool $short = false
    ): string {
        return 'Something went wrong…';
    }
}
