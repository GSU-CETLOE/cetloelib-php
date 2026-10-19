<?php

declare(strict_types=1);

namespace Cetloe\Tests\Console;

use Cetloe\Console\ApplicationFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

final class ApplicationFactoryTest extends TestCase
{
    public function testCreateConfiguresNameVersionAndLoader(): void
    {
        $loader = new FactoryCommandLoader([]);

        $application = (new ApplicationFactory())->create('tool', '1.2.3', $loader);

        self::assertSame('tool', $application->getName());
        self::assertSame('1.2.3', $application->getVersion());
        self::assertFalse($application->isAutoExitEnabled());
        self::assertTrue($application->areExceptionsCaught());
    }


    public function testMissingNameAndVersionBecomeUnknown(): void
    {
        $application = (new ApplicationFactory())->create(null, null, new FactoryCommandLoader([]));

        self::assertSame('UNKNOWN', $application->getName());
        self::assertSame('UNKNOWN', $application->getVersion());
    }
}
