<?php

declare(strict_types=1);

namespace Cetloe\Tests\Logger;

use Cetloe\Logger\LoggerConfig;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;

final class LoggerConfigTest extends EnvironmentTestCase
{
    public function testCreateFallsBackToApplicationNameAndNulls(): void
    {
        $config = LoggerConfig::create(FixtureApp::createEnv());

        self::assertSame(FixtureApp::NAME, $config->logName);
        self::assertNull($config->logDir);
        self::assertNull($config->logLevel);
        self::assertNull($config->logFileFormat);
        self::assertNull($config->logFormat);
    }


    public function testCreateReadsEveryLogVariable(): void
    {
        $config = LoggerConfig::create(FixtureApp::createEnv([
            LoggerConfig::LOG_NAME => 'custom',
            LoggerConfig::LOG_DIR => '/var/log',
            LoggerConfig::LOG_LEVEL => 'debug',
            LoggerConfig::LOG_FILE_FORMAT => '%s/%s-%s.txt',
            LoggerConfig::LOG_FORMAT => '%message%',
        ]));

        self::assertSame('custom', $config->logName);
        self::assertSame('/var/log', $config->logDir);
        self::assertSame('debug', $config->logLevel);
        self::assertSame('%s/%s-%s.txt', $config->logFileFormat);
        self::assertSame('%message%', $config->logFormat);
    }
}
