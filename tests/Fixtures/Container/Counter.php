<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\Container;

/**
 * Minimal autowirable class for the definition helper tests.
 */
final class Counter
{
    public function __construct(public readonly int $start = 0)
    {
    }
}
