<?php

declare(strict_types=1);

namespace Cetloe\Tests\Logger;

use Cetloe\Container\ContainerFactory;
use Cetloe\Logger\Handler\FileHandler;
use Cetloe\Logger\LoggerConfig;
use Cetloe\Logger\LoggerDefinitionSource;
use Cetloe\Logger\Processor\ElapsedTimeProcessor;
use Cetloe\Tests\Fixtures\App\Service\Greeter;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;
use Cetloe\Tests\Support\TempDir;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

final class LoggerDefinitionSourceTest extends EnvironmentTestCase
{
    private string $logDir;


    protected function setUp(): void
    {
        parent::setUp();
        $this->logDir = TempDir::create();
    }


    protected function tearDown(): void
    {
        TempDir::remove($this->logDir);
        parent::tearDown();
    }


    public function testLoggerServicesResolveFromTheContainer(): void
    {
        $env = FixtureApp::createEnv([LoggerConfig::LOG_DIR => $this->logDir, LoggerConfig::LOG_LEVEL => 'INFO']);
        $container = (new ContainerFactory($env))->create([LoggerDefinitionSource::class]);

        $logger = $container->get(LoggerInterface::class);
        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame(FixtureApp::NAME, $logger->getName());
        self::assertInstanceOf(FileHandler::class, $container->get(HandlerInterface::class));
        self::assertInstanceOf(ElapsedTimeProcessor::class, $container->get(ProcessorInterface::class));
        self::assertSame($this->logDir, self::resolve($container, LoggerConfig::class)->logDir);
    }


    public function testLoggerAwareApplicationClassesReceiveTheLogger(): void
    {
        $env = FixtureApp::createEnv([LoggerConfig::LOG_DIR => $this->logDir, LoggerConfig::LOG_LEVEL => 'INFO']);
        $container = (new ContainerFactory($env))->create([LoggerDefinitionSource::class]);

        $greeter = self::resolve($container, Greeter::class);
        $logger = $greeter->getLogger();
        self::assertSame($container->get(LoggerInterface::class), $logger);
        self::assertInstanceOf(LoggerInterface::class, $logger);

        $logger->info('greeting logged');

        $logFile = $this->logDir . '/' . FixtureApp::NAME . '.' . date('Y_m_d') . '.log';
        self::assertStringContainsString('[INFO]: greeting logged', (string)file_get_contents($logFile));
    }
}
