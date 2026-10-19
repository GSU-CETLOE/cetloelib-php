<?php

declare(strict_types=1);

namespace Cetloe\Tests\Logger;

use Cetloe\Logger\Processor\ElapsedTimeProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

final class ElapsedTimeProcessorTest extends TestCase
{
    public function testElapsedTimeSinceCreationIsAddedToExtra(): void
    {
        $processor = new ElapsedTimeProcessor();
        $record = new LogRecord(new \DateTimeImmutable(), 'chan', Level::Info, 'msg');

        $processed = $processor($record);

        self::assertIsString($processed->extra['elapsed']);
        self::assertMatchesRegularExpression('/^00:00:00\.\d{3}$/', $processed->extra['elapsed']);
    }
}
