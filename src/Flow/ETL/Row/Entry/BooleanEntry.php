<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry;

use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\Row\Entry;

/**
 * @psalm-immutable
 */
final class BooleanEntry implements Entry
{
    private string $key;

    private string $name;

    private bool $value;

    public function __construct(string $name, bool $value)
    {
        if (empty($name)) {
            throw InvalidArgumentException::because('Entry name cannot be empty');
        }

        $this->key = \mb_strtolower($name);
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @param bool|int|string $value
     */
    public static function from(string $name, $value) : self
    {
        if (\is_bool($value)) {
            return new self($name, $value);
        }

        $value = \mb_strtolower(\trim((string) $value));

        if (!\in_array($value, ['1', '0', 'true', 'false', 'yes', 'no'], true)) {
            throw InvalidArgumentException::because('Value "%s" can\'t be casted to boolean.', $value);
        }

        if ($value === 'true' || $value === 'yes') {
            return new self($name, true);
        }

        if ($value === 'false' || $value === 'no') {
            return new self($name, false);
        }

        return new self($name, (bool) $value);
    }

    public function name() : string
    {
        return $this->name;
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function value() : bool
    {
        return $this->value;
    }

    public function is(string $name) : bool
    {
        return $this->key === \mb_strtolower($name);
    }

    public function rename(string $name) : Entry
    {
        return new self($name, $this->value);
    }

    /**
     * @psalm-suppress MixedArgument
     */
    public function map(callable $mapper) : Entry
    {
        return new self($this->name, $mapper($this->value()));
    }

    public function isEqual(Entry $entry) : bool
    {
        return $this->is($entry->name()) && $entry instanceof self && $this->value() === $entry->value();
    }
}
