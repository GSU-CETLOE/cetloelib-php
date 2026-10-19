<?php

declare(strict_types=1);

namespace Cetloe\Tests\Logger;

use Cetloe\Logger\LoggerConfig;
use Cetloe\Logger\LoggerFactory;
use Cetloe\Logger\Processor\ElapsedTimeProcessor;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

final class LoggerFactoryTest extends TestCase
{
    public function testCreateWiresNameHandlerAndProcessor(): void
    {
        $handler = new TestHandler();
        $processor = new ElapsedTimeProcessor();

        $logger = (new LoggerFactory())->create(
            new LoggerConfig('factory-test', null, null, null, null),
            $handler,
            $processor
        );

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame('factory-test', $logger->getName());
        self::assertSame([$handler], $logger->getHandlers());
        self::assertSame([$processor], $logger->getProcessors());
    }
}
