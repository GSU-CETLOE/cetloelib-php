<?php

declare(strict_types=1);

namespace mjfklib\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    /**
     * @param LoggerConfig $config
     * @param HandlerInterface $handler
     * @param ProcessorInterface $processor
     * @return LoggerInterface
     */
    public function create(
        LoggerConfig $config,
        HandlerInterface $handler,
        ProcessorInterface $processor
    ): LoggerInterface {
        return (new Logger($config->logName))
            ->pushHandler($handler)
            ->pushProcessor($processor);
    }
}
