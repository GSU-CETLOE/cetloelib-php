<?php

declare(strict_types=1);

namespace Cetloe\Tests\Console;

use Cetloe\Console\ConsoleDefinitionSource;
use Cetloe\Container\ContainerFactory;
use Cetloe\Logger\LoggerConfig;
use Cetloe\Logger\LoggerDefinitionSource;
use Cetloe\Tests\Fixtures\App\Command\FailCommand;
use Cetloe\Tests\Fixtures\App\Command\HelloCommand;
use Cetloe\Tests\Fixtures\App\Command\QuietCommand;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;
use Cetloe\Tests\Support\TempDir;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleDefinitionSourceTest extends EnvironmentTestCase
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


    public function testLoggerSourceIsDeclaredAsADependency(): void
    {
        $source = new ConsoleDefinitionSource(FixtureApp::createEnv());

        self::assertSame([LoggerDefinitionSource::class], $source->getSources());
    }


    public function testConsoleServicesResolveFromTheContainer(): void
    {
        $env = FixtureApp::createEnv([LoggerConfig::LOG_DIR => $this->logDir, 'APP_VERSION' => '9.9']);
        $container = (new ContainerFactory($env))->create([ConsoleDefinitionSource::class]);

        self::assertInstanceOf(ArgvInput::class, $container->get(InputInterface::class));
        self::assertInstanceOf(ConsoleOutput::class, $container->get(OutputInterface::class));

        $application = self::resolve($container, Application::class);
        self::assertSame(FixtureApp::NAME, $application->getName());
        self::assertSame('9.9', $application->getVersion());

        $loader = self::resolve($container, CommandLoaderInterface::class);
        self::assertTrue($loader->has(HelloCommand::NAME));
        self::assertTrue($loader->has(FailCommand::NAME));
        self::assertTrue($loader->has(QuietCommand::NAME));
        self::assertInstanceOf(HelloCommand::class, $loader->get(HelloCommand::NAME));
    }
}
