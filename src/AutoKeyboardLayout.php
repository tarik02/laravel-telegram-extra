<?php

namespace Tarik02\LaravelTelegramExtra;

use Tarik02\Telegram\Collections\{
    InlineKeyboardButtonCollectionCollection,
    KeyboardButtonCollectionCollection
};
use Tarik02\Telegram\Entities\{
    InlineKeyboardButton,
    KeyboardButton
};

/**
 * Class AutoKeyboardLayout
 * @package Tarik02\LaravelTelegramExtra
 */
final class AutoKeyboardLayout
{
    const MAX_BUTTONS_PER_ROW = 8;

    private int $maxButtonsPerRow = self::MAX_BUTTONS_PER_ROW;

    /**
     * @var array
     */
    private array $groups = [];

    /**
     * @return void
     */
    public function __construct()
    {
        $this->groups[] = [
            'buttons' => [],
            'wrap' => true,
        ];
    }

    /**
     * @param ...$buttons
     * @return self
     */
    public function push(...$buttons): self
    {
        \array_push(
            $this->groups[\count($this->groups) - 1]['buttons'],
            ...$buttons
        );

        return $this;
    }

    /**
     * @return self
     */
    public function break(): self
    {
        $this->groups[] = \array_merge(
            $this->groups[\count($this->groups) - 1],
            [
                'buttons' => [],
            ]
        );

        return $this;
    }

    /**
     * @return self
     */
    public function wrap(): self
    {
        $this->groups[\count($this->groups) - 1]['wrap'] = true;

        return $this;
    }

    /**
     * @return self
     */
    public function nowrap(): self
    {
        $this->groups[\count($this->groups) - 1]['wrap'] = false;

        return $this;
    }

    /**
     * @return KeyboardButtonCollectionCollection|InlineKeyboardButtonCollectionCollection|null
     */
    public function build()
    {
        foreach ($this->groups as $group) {
            /** @var KeyboardButton|InlineKeyboardButton $button */
            foreach ($group['buttons'] as $button) {
                $result = $button->collection()->collection();

                break 2;
            }
        }

        if (! isset($result)) {
            return null;
        }

        foreach ($this->groups as $group) {
            if (\count($group['buttons']) === 0) {
                continue;
            }

            $chunks = \array_chunk(
                $group['buttons'],
                ($group['wrap']
                    ? self::determineCountPerRow(
                        \count($group['buttons']),
                        $this->maxButtonsPerRow
                    )
                    : $this->maxButtonsPerRow
                )
            );

            foreach ($chunks as $rowButtons) {
                $rowButtonsCollection = \get_class($result)::makeItem();

                foreach ($rowButtons as $button) {
                    $rowButtonsCollection = $rowButtonsCollection->push(
                        $button
                    );
                }

                $result = $result->push(
                    $rowButtonsCollection
                );
            }
        }

        return $result;
    }

    /**
     * @param int $count
     * @param int $maxButtonsPerRow
     * @return int
     */
    private static function determineCountPerRow(int $count, int $maxButtonsPerRow): int
    {
        if ($count <= 3) {
            return $count;
        }

        for ($i = 3; $i <= $count; ++$i) {
            if (
                $count % $i === 0 &&
                $count / $i >= 3 &&
                $count / $i <= $maxButtonsPerRow
            ) {
                return $count / $i;
            }
        }

        return \max(
            1,
            \min(
                $maxButtonsPerRow,
                \floor(\sqrt($count))
            )
        );
    }
}
