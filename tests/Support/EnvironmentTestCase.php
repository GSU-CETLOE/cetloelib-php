<?php

declare(strict_types=1);

namespace Cetloe\Tests\Support;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Base class for tests that touch process-wide state: $_ENV, $_SERVER, the SHELL_VERBOSITY variable, and the working
 * directory. Everything is snapshotted before each test and restored afterwards.
 */
abstract class EnvironmentTestCase extends TestCase
{
    /** @var array<string,mixed> */
    private array $savedEnv = [];

    /** @var array<string,mixed> */
    private array $savedServer = [];

    private string $savedCwd = '';

    private string|false $savedShellVerbosity = false;


    protected function setUp(): void
    {
        $this->savedEnv = $_ENV;
        $this->savedServer = $_SERVER;
        $cwd = getcwd();
        if ($cwd === false) {
            throw new \RuntimeException('Unable to read working directory');
        }
        $this->savedCwd = $cwd;
        $this->savedShellVerbosity = getenv('SHELL_VERBOSITY');
    }


    protected function tearDown(): void
    {
        $_ENV = $this->savedEnv;
        $_SERVER = $this->savedServer;
        chdir($this->savedCwd);
        putenv(
            $this->savedShellVerbosity === false
                ? 'SHELL_VERBOSITY'
                : 'SHELL_VERBOSITY=' . $this->savedShellVerbosity
        );
    }


    /**
     * Resolves a container entry and fails the test unless it is an instance of the expected class.
     *
     * @template T of object
     * @param ContainerInterface $container
     * @param class-string<T> $class
     * @return T
     */
    protected static function resolve(ContainerInterface $container, string $class): object
    {
        $entry = $container->get($class);
        self::assertInstanceOf($class, $entry);
        return $entry;
    }


    /**
     * Captures PHP warnings and notices raised while running the callback, so the test can assert on them instead of
     * letting them escape as test output.
     *
     * @param callable(): void $callback
     * @return string[] captured error messages
     */
    protected function captureWarnings(callable $callback): array
    {
        $messages = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$messages): bool {
            $messages[] = $errstr;
            return true;
        });

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $messages;
    }
}
