<?php

declare(strict_types=1);

namespace Cetloe\Tests\Console;

use Cetloe\Console\ApplicationInvoker;
use Cetloe\Container\Env;
use Cetloe\Logger\LoggerConfig;
use Cetloe\Tests\Fixtures\App\Command\FailCommand;
use Cetloe\Tests\Fixtures\App\Command\HelloCommand;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;
use Cetloe\Tests\Support\TempDir;
use DI\Container;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ApplicationInvokerTest extends EnvironmentTestCase
{
    private string $logDir;


    protected function setUp(): void
    {
        parent::setUp();
        $this->logDir = TempDir::create();
        $_ENV[LoggerConfig::LOG_DIR] = $this->logDir;
    }


    protected function tearDown(): void
    {
        TempDir::remove($this->logDir);
        parent::tearDown();
    }


    /**
     * Invoker whose container reads the command from an array and writes to a buffer instead of the real terminal.
     *
     * @param array<string,mixed> $input
     * @return array{0:ApplicationInvoker,1:BufferedOutput}
     */
    private function createInvoker(array $input): array
    {
        $invoker = new class extends ApplicationInvoker {
            /** @var array<string,mixed> */
            public array $input = [];

            public BufferedOutput $output;


            /**
             * @inheritdoc
             */
            protected function createContainer(Env $env): Container
            {
                $container = parent::createContainer($env);
                $container->set(InputInterface::class, new ArrayInput($this->input));
                $container->set(OutputInterface::class, $this->output);
                return $container;
            }
        };
        $invoker->input = $input;
        $invoker->output = new BufferedOutput();

        return [$invoker, $invoker->output];
    }


    public function testInvokeRunsTheCommandFromTheApplicationDirectory(): void
    {
        [$invoker, $output] = $this->createInvoker(['command' => HelloCommand::NAME, 'names' => ['alice']]);

        $exitCode = $invoker->invoke(FixtureApp::dir(), FixtureApp::NAMESPACE, FixtureApp::NAME, 'test');

        self::assertSame(0, $exitCode);
        self::assertSame("Hello alice\n", $output->fetch());
        self::assertSame(FixtureApp::dir(), getcwd());
    }


    public function testInvokeReturnsTheCommandExitCodeOnFailure(): void
    {
        [$invoker] = $this->createInvoker(['command' => FailCommand::NAME]);

        $exitCode = $invoker->invoke(FixtureApp::dir(), FixtureApp::NAMESPACE, FixtureApp::NAME, 'test');

        self::assertSame(1, $exitCode);
    }
}
