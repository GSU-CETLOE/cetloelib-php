<?php

declare(strict_types=1);

namespace Cetloe\Tests\Container;

use Cetloe\Container\ArrayValue;
use PHPUnit\Framework\TestCase;

/**
 * Covers the container-level ArrayValue, which is itself deprecated.
 *
 * @deprecated Goes away together with Cetloe\Container\ArrayValue; the deprecation marker keeps PHPStan's
 * deprecation rules from flagging the intentional calls below.
 */
final class ArrayValueTest extends TestCase
{
    public function testArrayAndNameLookUpAKeyLikeTheUtilsVersion(): void
    {
        self::assertSame(['a' => '1'], ArrayValue::getStringArray(['k' => ['a' => 1]], 'k'));
    }


    public function testArrayAndNameRejectUnusableValue(): void
    {
        $this->expectException(\ValueError::class);
        ArrayValue::getStringArray(['k' => 5], 'k');
    }


    public function testOtherShapesMergeValuesOverName(): void
    {
        self::assertSame(
            ['a' => 'values', 'b' => '2'],
            ArrayValue::getStringArray(['a' => 'values'], ['a' => 'name', 'b' => 2])
        );
        self::assertSame(['x' => 'y'], ArrayValue::getStringArray('{"x":"y"}'));
        self::assertSame([], ArrayValue::getStringArray(null, null));
    }
}
