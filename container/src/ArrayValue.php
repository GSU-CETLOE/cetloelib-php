<?php

declare(strict_types=1);

namespace mjfklib\Container;

/**
 * Class is being maintained for backwards compatibility.
 * @deprecated
 */
final class ArrayValue extends \mjfklib\Utils\ArrayValue
{
    /**
     * @param mixed[]|string|object|null $values
     * @param mixed[]|string|object|null $name
     * @return array<string,string>
     */
    public static function getStringArray(
        array|string|object|null $values,
        array|string|object|null $name = null
    ): array {
        if (is_array($values) && is_string($name)) {
            return self::getValue(
                $values,
                $name,
                fn () => false,
                fn ($v) => self::getStringArrayValue($v) ?? throw self::err($name)
            );
        }

        return array_merge(
            self::getStringArrayValue($name) ?? [],
            self::getStringArrayValue($values) ?? []
        );
    }
}
