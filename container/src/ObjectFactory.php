<?php

declare(strict_types=1);

namespace mjfklib\Container;

use mjfklib\Utils\ArrayValue;

final class ObjectFactory
{
    /**
     * @template T
     * @param mixed $values
     * @param class-string<T> $className
     * @param (callable(mixed[] $values): T) $construct
     * @return T
     */
    public static function createObject(
        mixed $values,
        string $className,
        callable $construct
    ): mixed {
        try {
            return is_object($values) && is_a($values, $className, false)
                ? $values
                : $construct(
                    ArrayValue::convertToArray($values)
                );
        } catch (\Throwable $t) {
            throw new \RuntimeException(
                message: "Error creating instance: {$className}}",
                previous: $t
            );
        }
    }
}
