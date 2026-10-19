<?php

declare(strict_types=1);

namespace Cetloe\Tests\Logger;

use Cetloe\Logger\Handler\FileHandler;
use Cetloe\Logger\LoggerConfig;
use Cetloe\Tests\Support\TempDir;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FileHandlerTest extends TestCase
{
    private string $dir;


    protected function setUp(): void
    {
        $this->dir = TempDir::create();
    }


    protected function tearDown(): void
    {
        TempDir::remove($this->dir);
    }


    /**
     * @param string|null $level
     * @param string|null $fileFormat
     * @param string|null $format
     * @return LoggerConfig
     */
    private function config(?string $level = null, ?string $fileFormat = null, ?string $format = null): LoggerConfig
    {
        return new LoggerConfig('app', $this->dir, $level, $fileFormat, $format);
    }


    /**
     * @return array<int|string,array{0:string,1:Level}>
     */
    public static function levels(): array
    {
        return [
            'DEBUG' => ['DEBUG', Level::Debug],
            'debug lowercase' => ['debug', Level::Debug],
            '100' => ['100', Level::Debug],
            '128' => ['128', Level::Debug],
            '256' => ['256', Level::Debug],
            'INFO' => ['INFO', Level::Info],
            '200' => ['200', Level::Info],
            '64' => ['64', Level::Info],
            'NOTICE' => ['NOTICE', Level::Notice],
            '250' => ['250', Level::Notice],
            '32' => ['32', Level::Notice],
            'WARNING' => ['WARNING', Level::Warning],
            '300' => ['300', Level::Warning],
            'ERROR' => ['ERROR', Level::Error],
            '400' => ['400', Level::Error],
            'CRITICAL' => ['CRITICAL', Level::Critical],
            '500' => ['500', Level::Critical],
            'ALERT' => ['ALERT', Level::Alert],
            '550' => ['550', Level::Alert],
            'EMERGENCY' => ['EMERGENCY', Level::Emergency],
            '600' => ['600', Level::Emergency],
        ];
    }


    /**
     * @param string $configured
     * @param Level $expected
     */
    #[DataProvider('levels')]
    public function testLevelNamesAndNumbersAreMapped(string $configured, Level $expected): void
    {
        self::assertSame($expected, (new FileHandler($this->config($configured)))->getLevel());
    }


    public function testLevelDefaultsToError(): void
    {
        self::assertSame(Level::Error, (new FileHandler($this->config()))->getLevel());
    }


    public function testUnknownLevelIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        new FileHandler($this->config('LOUD'));
    }


    public function testLogFileIsNamedFromDirectoryNameAndDate(): void
    {
        $handler = new FileHandler($this->config());

        self::assertSame($this->dir . '/app.' . date('Y_m_d') . '.log', $handler->getUrl());
    }


    public function testFileFormatCanBeOverridden(): void
    {
        $handler = new FileHandler($this->config(null, '%s/%s-%s.txt'));

        self::assertSame($this->dir . '/app-' . date('Y_m_d') . '.txt', $handler->getUrl());
    }


    public function testStdoutIsUsedWhenNoDirectoryIsConfigured(): void
    {
        $handler = new FileHandler(new LoggerConfig('app', null, null, null, null));

        self::assertSame(FileHandler::DEFAULT_LOG_FILE_PATH, $handler->getUrl());
    }


    public function testLineFormatUsesDefaultOrConfiguredPattern(): void
    {
        $logger = new Logger('chan');
        $logger->pushHandler(new FileHandler($this->config('INFO')));
        $logger->pushProcessor(function ($record) {
            $record->extra['elapsed'] = '00:00:00.000';
            return $record;
        });
        $logger->info('first');

        $custom = new Logger('chan');
        $custom->pushHandler(new FileHandler($this->config('INFO', '%s/%s-%s.custom', '>> %message%')));
        $custom->info('second');

        $defaultLine = (string)file_get_contents($this->dir . '/app.' . date('Y_m_d') . '.log');
        self::assertMatchesRegularExpression('/^\[[^\]]+\]\[00:00:00\.000\]\[chan\]\[INFO\]: first\n$/', $defaultLine);
        self::assertSame(">> second\n", file_get_contents($this->dir . '/app-' . date('Y_m_d') . '.custom'));
        self::assertInstanceOf(LineFormatter::class, (new FileHandler($this->config()))->getFormatter());
    }
}
