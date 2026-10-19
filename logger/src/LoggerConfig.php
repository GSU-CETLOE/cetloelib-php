<?php

declare(strict_types=1);

namespace mjfklib\Logger;

use mjfklib\Container\Env;

class LoggerConfig
{
    public const LOG_DIR = 'LOG_DIR';
    public const LOG_NAME = 'LOG_NAME';
    public const LOG_LEVEL = 'LOG_LEVEL';
    public const LOG_FILE_FORMAT = 'LOG_FILE_FORMAT';
    public const LOG_FORMAT = 'LOG_FORMAT';


    /**
     * @param Env $env
     * @return self
     */
    public static function create(Env $env): self
    {
        return new self(
            $env[self::LOG_NAME] ?? $env->appName,
            $env[self::LOG_DIR] ?? null,
            $env[self::LOG_LEVEL] ?? null,
            $env[self::LOG_FILE_FORMAT] ?? null,
            $env[self::LOG_FORMAT] ?? null
        );
    }


    /**
     * @param string $logName
     * @param string|null $logDir
     * @param string|null $logLevel
     * @param string|null $logFileFormat
     * @param string|null $logFormat
     */
    public function __construct(
        public string $logName,
        public string|null $logDir,
        public string|null $logLevel,
        public string|null $logFileFormat,
        public string|null $logFormat
    ) {
    }
}
