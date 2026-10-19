<?php

declare(strict_types=1);

namespace mjfklib\Logger\Handler;

use mjfklib\Logger\LoggerConfig;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

class FileHandler extends StreamHandler
{
    public const DEFAULT_LOG_LEVEL = 'ERROR';
    public const DEFAULT_LOG_FILE_PATH = 'php://stdout';
    public const DEFAULT_LOG_FILE_FORMAT = '%s/%s.%s.log';
    public const DEFAULT_LOG_FORMAT = '[%datetime%][%extra.elapsed%][%channel%][%level_name%]: %message%';


    /**
     * @param LoggerConfig $config
     * @return void
     */
    public function __construct(LoggerConfig $config)
    {
        $logLevel = $config->logLevel ?? static::DEFAULT_LOG_LEVEL;
        $logFileFormat = $config->logFileFormat ?? static::DEFAULT_LOG_FILE_FORMAT;
        $logFormat = $config->logFormat ?? static::DEFAULT_LOG_FORMAT;

        parent::__construct(
            ($config->logDir === null)
                ? static::DEFAULT_LOG_FILE_PATH
                : sprintf(
                    $logFileFormat,
                    $config->logDir,
                    $config->logName,
                    date('Y_m_d')
                ),
            match (strtoupper($logLevel)) {
                '256', '128', '100', 'DEBUG' => Level::Debug,
                '64', '200', 'INFO'          => Level::Info,
                '32', '250', 'NOTICE'        => Level::Notice,
                '300', 'WARNING'             => Level::Warning,
                '400', 'ERROR'               => Level::Error,
                '500', 'CRITICAL'            => Level::Critical,
                '550', 'ALERT'               => Level::Alert,
                '600', 'EMERGENCY'           => Level::Emergency,
                default                      => throw new \RuntimeException()
            }
        );

        $this->setFormatter(new LineFormatter($logFormat . "\n"));
    }
}
