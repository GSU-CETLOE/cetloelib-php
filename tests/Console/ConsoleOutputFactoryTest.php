<?php

declare(strict_types=1);

namespace Cetloe\Tests\Console;

use Cetloe\Console\Output\ConsoleOutputFactory;
use Cetloe\Tests\Support\EnvironmentTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleOutputFactoryTest extends EnvironmentTestCase
{
    /**
     * @return array<string,array{0:string[],1:int,2:int}>
     */
    public static function verbosityOptions(): array
    {
        return [
            'default' => [[], OutputInterface::VERBOSITY_NORMAL, 0],
            '--quiet' => [['--quiet'], OutputInterface::VERBOSITY_QUIET, -1],
            '-q' => [['-q'], OutputInterface::VERBOSITY_QUIET, -1],
            '-v' => [['-v'], OutputInterface::VERBOSITY_VERBOSE, 1],
            '-vv' => [['-vv'], OutputInterface::VERBOSITY_VERY_VERBOSE, 2],
            '-vvv' => [['-vvv'], OutputInterface::VERBOSITY_DEBUG, 3],
            '--verbose=1' => [['--verbose=1'], OutputInterface::VERBOSITY_VERBOSE, 1],
            '--verbose=2' => [['--verbose=2'], OutputInterface::VERBOSITY_VERY_VERBOSE, 2],
            '--verbose=3' => [['--verbose=3'], OutputInterface::VERBOSITY_DEBUG, 3],
            '--verbose 1' => [['--verbose', '1'], OutputInterface::VERBOSITY_NORMAL, 0],
        ];
    }


    /**
     * @param string[] $options
     * @param int $expectedVerbosity
     * @param int $expectedShellVerbosity
     */
    #[DataProvider('verbosityOptions')]
    public function testVerbosityFollowsCommandLineOptions(
        array $options,
        int $expectedVerbosity,
        int $expectedShellVerbosity
    ): void {
        putenv('SHELL_VERBOSITY');

        $output = (new ConsoleOutputFactory())->create(new ArgvInput(['bin', ...$options]));

        self::assertSame($expectedVerbosity, $output->getVerbosity());
        self::assertSame((string)$expectedShellVerbosity, getenv('SHELL_VERBOSITY'));
        self::assertSame($expectedShellVerbosity, $_ENV['SHELL_VERBOSITY']);
        self::assertSame($expectedShellVerbosity, $_SERVER['SHELL_VERBOSITY']);
    }


    /**
     * @return array<int|string,array{0:string,1:int}>
     */
    public static function shellVerbosity(): array
    {
        return [
            '-1' => ['-1', OutputInterface::VERBOSITY_QUIET],
            '0' => ['0', OutputInterface::VERBOSITY_NORMAL],
            '1' => ['1', OutputInterface::VERBOSITY_VERBOSE],
            '2' => ['2', OutputInterface::VERBOSITY_VERY_VERBOSE],
            '3' => ['3', OutputInterface::VERBOSITY_DEBUG],
            'junk' => ['junk', OutputInterface::VERBOSITY_NORMAL],
        ];
    }


    /**
     * @param string $shellVerbosity
     * @param int $expectedVerbosity
     */
    #[DataProvider('shellVerbosity')]
    public function testVerbosityFallsBackToShellVerbosityVariable(string $shellVerbosity, int $expectedVerbosity): void
    {
        putenv('SHELL_VERBOSITY=' . $shellVerbosity);

        $output = (new ConsoleOutputFactory())->create(new ArgvInput(['bin']));

        self::assertSame($expectedVerbosity, $output->getVerbosity());
    }


    public function testAnsiOptionsControlDecoration(): void
    {
        $factory = new ConsoleOutputFactory();

        self::assertTrue($factory->create(new ArgvInput(['bin', '--ansi']))->isDecorated());
        self::assertFalse($factory->create(new ArgvInput(['bin', '--no-ansi']))->isDecorated());
    }
}
