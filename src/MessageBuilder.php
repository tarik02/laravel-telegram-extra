<?php

namespace Tarik02\LaravelTelegramExtra;

use Tarik02\Telegram\Collections\MessageEntityCollection;

use Tarik02\Telegram\Entities\{
    MessageEntity,
    User
};
use Tarik02\Telegram\Methods\{
    EditMessageText,
    SendMessage
};

/**
 * Class MessageBuilder
 * @package Tarik02\LaravelTelegramExtra
 */
class MessageBuilder
{
    /**
     * @var array
     */
    protected array $state = [
        'bold' => false,
        'italic' => false,
        'underline' => false,
        'strikethrough' => false,
    ];

    /**
     * @var array
     */
    protected array $stack = [];

    /**
     * @var string
     */
    protected string $text = '';

    /**
     * @var int
     */
    protected int $offset = 0;

    /**
     * @var MessageEntityCollection
     */
    protected MessageEntityCollection $entities;

    /**
     * @return static
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * @return void
     */
    public function __construct()
    {
        $this->entities = MessageEntityCollection::make();
    }

    /**
     * Pushes state to the stack (bold, italic, underline, strikethrough).
     *
     * @return $this
     */
    public function push(): self
    {
        $this->stack[] = $this->state;

        return $this;
    }

    /**
     * Restores state from the stack (bold, italic, underline, strikethrough).
     *
     * @return $this
     */
    public function pop(): self
    {
        $state = \array_pop($this->stack);

        return ($this
            ->bold($state['bold'] !== false)
            ->italic($state['italic'] !== false)
            ->underline($state['underline'] !== false)
            ->strikethrough($state['strikethrough'] !== false)
        );
    }

    /**
     * Resets state to default (bold, italic, underline, strikethrough are all disabled).
     *
     * @return $this
     */
    public function reset(): self
    {
        return ($this
            ->bold(false)
            ->italic(false)
            ->underline(false)
            ->strikethrough(false)
        );
    }

    /**
     * @param callable $callback
     * @param mixed ...$args
     * @return $this
     */
    public function tap(callable $callback, ...$args): self
    {
        $callback($this, ...$args);

        return $this;
    }

    /**
     * @param MessageBuilder $other
     * @return $this
     */
    public function concat(MessageBuilder $other): self
    {
        $this->applyEntities();

        $this->text .= $other->getText();

        foreach ($other->getEntities() as $entity) {
            $this->entities = $this->entities->push(
                $entity
                    ->withOffset($this->offset + $entity->offset())
            );
        }

        $this->offset += $other->offset;

        foreach ($this->state as &$offset) {
            if ($offset !== false) {
                $offset = $this->offset;
            }
        }

        return $this;
    }

    /**
     * Prints text.
     *
     * @param string $text
     * @return $this
     */
    public function text(string $text): self
    {
        return $this->pushText($text);
    }

    /**
     * Prints text and then a newline.
     *
     * @param string $text
     * @return $this
     */
    public function line(string $text = ''): self
    {
        return $this->text($text . "\n");
    }

    /**
     * Prints text and then a newline.
     *
     * @param string $text
     * @return $this
     */
    public function newline(string $text = ''): self
    {
        return $this->text($text . "\n");
    }

    /**
     * Mention a user by an username (`@username`).
     * Argument should not contain '@' symbol.
     *
     * @param string $username
     * @return $this
     */
    public function mention(string $username): self
    {
        return $this->pushText(
            '@' . $username,
            MessageEntity::make()
                ->withType('mention')
        );
    }

    /**
     * Prints a hashtag (`#hashtag`).
     * Argument should not contain '#' symbol.
     *
     * @param string $text
     * @return $this
     */
    public function hashtag(string $text): self
    {
        return $this->pushText(
            '#' . $text,
            MessageEntity::make()
                ->withType('hashtag')
        );
    }

    /**
     * Prints a cashtag (`$USD`).
     * Argument should not contain '#' symbol.
     *
     * @param string $text
     * @return $this
     */
    public function cashtag(string $text): self
    {
        return $this->pushText(
            '$' . $text,
            MessageEntity::make()
                ->withType('cashtag')
        );
    }

    /**
     * Prints a bot command (`/start@jobs_bot`).
     * Argument should not contain '/' or '@' or ' ' symbols.
     *
     * @param string $command
     * @param string|null $botUsername
     * @return $this
     */
    public function botCommand(string $command, ?string $botUsername = null): self
    {
        return $this->pushText(
            '/' . $command . ($botUsername !== null ? '@' . $botUsername : ''),
            MessageEntity::make()
                ->withType('bot_command')
        );
    }

    /**
     * Prints an url (`https://telegram.org`).
     *
     * @param string $url
     * @return $this
     */
    public function url(string $url): self
    {
        return $this->pushText(
            $url,
            MessageEntity::make()
                ->withType('url')
        );
    }

    /**
     * Prints an email (`do-not-reply@telegram.org`).
     *
     * @param string $email
     * @return $this
     */
    public function email(string $email): self
    {
        return $this->pushText(
            $email,
            MessageEntity::make()
                ->withType('email')
        );
    }

    /**
     * Prints a phone number (`+1-212-555-0123`).
     *
     * @param string $phoneNumber
     * @return $this
     */
    public function phoneNumber(string $phoneNumber): self
    {
        return $this->pushText(
            $phoneNumber,
            MessageEntity::make()
                ->withType('phone_number')
        );
    }

    /**
     * Makes next printed text bold (does opposite if `$flag` is `false`).
     *
     * @param bool $flag
     * @return $this
     */
    public function bold(bool $flag = true): self
    {
        if (($this->state['bold'] !== false) !== $flag) {
            if (! $flag) {
                $this->applyEntities(['bold']);
            }

            $this->state['bold'] = $flag ? $this->offset : false;
        }

        return $this;
    }

    /**
     * Makes next printed text italic (does opposite if `$flag` is `false`).
     *
     * @param bool $flag
     * @return $this
     */
    public function italic(bool $flag = true): self
    {
        if (($this->state['italic'] !== false) !== $flag) {
            if (! $flag) {
                $this->applyEntities(['italic']);
            }

            $this->state['italic'] = $flag ? $this->offset : false;
        }

        return $this;
    }

    /**
     * Makes next printed text underline (does opposite if `$flag` is `false`).
     *
     * @param bool $flag
     * @return $this
     */
    public function underline(bool $flag = true): self
    {
        if (($this->state['underline'] !== false) !== $flag) {
            if (! $flag) {
                $this->applyEntities(['underline']);
            }

            $this->state['underline'] = $flag ? $this->offset : false;
        }

        return $this;
    }

    /**
     * Makes next printed text strikethrough (does opposite if `$flag` is `false`).
     *
     * @param bool $flag
     * @return $this
     */
    public function strikethrough(bool $flag = true): self
    {
        if (($this->state['strikethrough'] !== false) !== $flag) {
            if (! $flag) {
                $this->applyEntities(['strikethrough']);
            }

            $this->state['strikethrough'] = $flag ? $this->offset : false;
        }

        return $this;
    }

    /**
     * Prints an inline code block.
     *
     * @param string $text
     * @return $this
     */
    public function code(string $text): self
    {
        return $this->pushText(
            $text,
            MessageEntity::make()
                ->withType('code')
        );
    }

    /**
     * Prints a code block.
     *
     * @param string $text
     * @param string|null $language
     * @return $this
     */
    public function pre(string $text, ?string $language = null): self
    {
        return $this->pushText(
            $text,
            MessageEntity::make()
                ->withType('pre')
                ->withLanguage($language)
        );
    }

    /**
     * Prints a link with custom caption. User will see the full url after clicking the link.
     *
     * @param string $text
     * @param string $url
     * @return $this
     */
    public function textLink(string $text, string $url): self
    {
        return $this->pushText(
            $text,
            MessageEntity::make()
                ->withType('text_link')
                ->withUrl($url)
        );
    }

    /**
     * Mentions a user without username.
     *
     * @param string $text
     * @param User $user
     * @return $this
     */
    public function textMention(string $text, User $user): self
    {
        return $this->pushText(
            $text,
            MessageEntity::make()
                ->withType('text_mention')
                ->withUser($user)
        );
    }

    /**
     * Returns text.
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Returns a colleciton of text entities.
     *
     * @return MessageEntityCollection
     */
    public function getEntities(): MessageEntityCollection
    {
        $this->applyEntities();

        return $this->entities;
    }

    /**
     * Returns a new SendMessage object.
     *
     * @param SendMessage|null $base
     * @return SendMessage
     */
    public function toSendMessage(?SendMessage $base = null): SendMessage
    {
        return ($base ?? SendMessage::make())
            ->withText($this->getText())
            ->withEntities($this->getEntities());
    }

    /**
     * Returns a new EditMessageText object.
     *
     * @param EditMessageText|null $base
     * @return EditMessageText
     */
    public function toEditMessageText(?EditMessageText $base = null): EditMessageText
    {
        return ($base ?? EditMessageText::make())
            ->withText($this->getText())
            ->withEntities($this->getEntities());
    }

    /**
     * @param string $text
     * @param MessageEntity|null $entity
     * @return $this
     */
    protected function pushText(string $text, ?MessageEntity $entity = null): self
    {
        if ($entity !== null) {
            $this->applyEntities();
        }

        $length = \strlen(\mb_convert_encoding($text, 'UTF-16', 'UTF-8')) / 2;

        if ($entity !== null) {
            $this->entities = $this->entities->push(
                $entity
                    ->withOffset($this->offset)
                    ->withLength($length)
            );
        }

        $this->text .= $text;
        $this->offset += $length;

        if ($entity !== null) {
            foreach ($this->state as &$offset) {
                if ($offset !== false) {
                    $offset = $this->offset;
                }
            }
        }

        return $this;
    }

    /**
     * @param array|null $only
     * @return void
     */
    protected function applyEntities(?array $only = null): void
    {
        foreach ($this->state as $type => &$offset) {
            if ($offset === false || $offset === $this->offset) {
                continue;
            }

            if ($only !== null && ! \in_array($type, $only)) {
                continue;
            }

            $this->entities = $this->entities->push(
                MessageEntity::make()
                    ->withType($type)
                    ->withOffset($offset)
                    ->withLength($this->offset - $offset)
            );

            $offset = $this->offset;
        }
    }
}
