<?php

namespace Tarik02\LaravelTelegramExtra;

/**
 * Class ParsedCommand
 * @package Tarik02\LaravelTelegramExtra
 */
final class ParsedCommand
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string[]
     */
    protected array $arguments;

    /**
     * @var string|null
     */
    protected ?string $receiver;

    /**
     * @param string $name
     * @param string[] $arguments
     * @param string|null $receiver
     * @return void
     */
    public function __construct(
        string $name,
        array $arguments = [],
        ?string $receiver = null
    ) {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->receiver = $receiver;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return int
     */
    public function argumentsCount(): int
    {
        return \count($this->arguments);
    }

    /**
     * @param int $index
     * @return string
     */
    public function argument(int $index): string
    {
        return $this->arguments[$index];
    }

    /**
     * @return string|null
     */
    public function receiver(): ?string
    {
        return $this->receiver;
    }

    /**
     * @param string $input
     * @return ParsedCommand|null
     */
    public static function parse(string $input): ?ParsedCommand
    {
        if (\preg_match('~^/([^\s]+)\s*(.*)$~', $input, $matches) === 0) {
            return null;
        }

        [$name, $receiver] = \array_merge(
            \explode('@', $matches[1], 2),
            [null]
        );
        $argumentsString = $matches[2];

        $arguments = (empty($argumentsString)
            ? []
            : \explode(' ', $argumentsString)
        );

        return new self(
            $name,
            $arguments,
            $receiver
        );
    }
}
