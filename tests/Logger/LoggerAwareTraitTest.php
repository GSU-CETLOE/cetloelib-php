<?php

declare(strict_types=1);

namespace Cetloe\Tests\Logger;

use Cetloe\Tests\Fixtures\Logging\UsesLoggerAwareTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class LoggerAwareTraitTest extends TestCase
{
    public function testSetLoggerComesFromThePsrTrait(): void
    {
        $subject = new UsesLoggerAwareTrait();
        $logger = new NullLogger();

        $subject->setLogger($logger);

        self::assertSame($logger, $subject->getLogger());
    }


    public function testElapsedTimeIsFormattedAsHoursMinutesSecondsMillis(): void
    {
        $subject = new UsesLoggerAwareTrait();

        self::assertMatchesRegularExpression('/^00:00:00\.\d{3}$/', $subject->elapsed(microtime(true)));
        self::assertMatchesRegularExpression('/^01:01:05\.\d{3}$/', $subject->elapsed(microtime(true) - 3665.5));
    }


    public function testResultsAreFormattedAsScalarPairs(): void
    {
        $subject = new UsesLoggerAwareTrait();

        self::assertSame(
            'rows: 3; ok: 1; name: x',
            $subject->format(['rows' => 3, 'ok' => true, 'skip' => [1], 'name' => 'x', 'none' => null])
        );
        self::assertSame('', $subject->format([]));
    }
}
