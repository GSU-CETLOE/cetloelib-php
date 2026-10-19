<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\App\Shape;

trait ShapeTrait
{
    /**
     * @return string
     */
    public function name(): string
    {
        return strtolower((new \ReflectionClass($this))->getShortName());
    }
}
