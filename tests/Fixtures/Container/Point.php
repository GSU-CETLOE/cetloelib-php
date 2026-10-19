<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\Container;

/**
 * Value object for the object factory tests.
 */
final class Point
{
    public function __construct(public readonly int $x, public readonly int $y)
    {
    }
}
