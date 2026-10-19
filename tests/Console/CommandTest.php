<?php

declare(strict_types=1);

namespace Cetloe\Tests\Console;

use Cetloe\Console\ConsoleDefinitionSource;
use Cetloe\Container\ContainerFactory;
use Cetloe\Logger\LoggerConfig;
use Cetloe\Tests\Fixtures\App\Command\FailCommand;
use Cetloe\Tests\Fixtures\App\Command\HelloCommand;
use Cetloe\Tests\Fixtures\App\Command\QuietCommand;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;
use Cetloe\Tests\Support\TempDir;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Runs the fixture commands through a real Application built by the container, so command loading, logger injection,
 * and the base Command's logging all get exercised together.
 */
final class CommandTest extends EnvironmentTestCase
{
    private string $logDir;

    private ApplicationTester $tester;


    protected function setUp(): void
    {
        parent::setUp();
        $this->logDir = TempDir::create();

        $env = FixtureApp::createEnv([LoggerConfig::LOG_DIR => $this->logDir, LoggerConfig::LOG_LEVEL => 'INFO']);
        $container = (new ContainerFactory($env))->create([ConsoleDefinitionSource::class]);
        $application = $container->get(Application::class);
        self::assertInstanceOf(Application::class, $application);
        $this->tester = new ApplicationTester($application);
    }


    protected function tearDown(): void
    {
        TempDir::remove($this->logDir);
        parent::tearDown();
    }


    /**
     * @return string[] lines written to the fixture application's log file
     */
    private function logLines(): array
    {
        $logFile = $this->logDir . '/' . FixtureApp::NAME . '.' . date('Y_m_d') . '.log';
        if (!is_file($logFile)) {
            return [];
        }

        $lines = explode("\n", (string)file_get_contents($logFile));
        return array_values(array_filter($lines, fn (string $line): bool => $line !== ''));
    }


    public function testSuccessfulCommandLogsStartAndFinish(): void
    {
        $exitCode = $this->tester->run(['command' => HelloCommand::NAME, 'names' => ['alice', 'bob']]);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertSame("Hello alice\nHello bob\n", $this->tester->getDisplay());

        $lines = $this->logLines();
        self::assertCount(2, $lines);
        self::assertStringContainsString("[INFO]: Started 'fixture:hello' alice bob", $lines[0]);
        self::assertStringContainsString("[INFO]: Finished 'fixture:hello' alice bob, code => 0", $lines[1]);
    }


    public function testInvalidArgumentValuesAreRejected(): void
    {
        $exitCode = $this->tester->run(['command' => HelloCommand::NAME, 'names' => ['alice', 'mallory']]);

        self::assertSame(SymfonyCommand::FAILURE, $exitCode);
        self::assertStringContainsString('Invalid argument values: mallory', $this->tester->getDisplay());
    }


    public function testFailingCommandLogsTheExceptionChainAndFinishesWithFailure(): void
    {
        $exitCode = $this->tester->run(['command' => FailCommand::NAME]);

        self::assertSame(SymfonyCommand::FAILURE, $exitCode);
        self::assertStringContainsString(FailCommand::MESSAGE, $this->tester->getDisplay());

        $lines = $this->logLines();
        self::assertStringContainsString("[INFO]: Started 'fixture:fail'", $lines[0]);
        self::assertStringContainsString(
            "[ERROR]: Error in 'fixture:fail' - RuntimeException: " . FailCommand::MESSAGE,
            $lines[1]
        );
        self::assertStringContainsString(
            '[ERROR]:  at Cetloe.Tests.Fixtures.App.Command.FailCommand.execute(',
            $lines[2]
        );
        self::assertStringContainsString('FailCommand.php:', $lines[2]);

        $allLines = implode("\n", $lines);
        self::assertStringContainsString('[ERROR]: Caused by: LogicException: ' . FailCommand::CAUSE, $allLines);
        self::assertMatchesRegularExpression('/\[ERROR\]:  \.\.\. \d+ more\n/', $allLines);
        self::assertStringContainsString("[INFO]: Finished 'fixture:fail', code => 1", $lines[count($lines) - 1]);
    }


    public function testLoggingCanBeDisabledPerCommand(): void
    {
        $exitCode = $this->tester->run(['command' => QuietCommand::NAME]);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertSame([], $this->logLines());
    }
}
