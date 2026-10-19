<?php

declare(strict_types=1);

namespace mjfklib\Logger;

use mjfklib\Container\DefinitionSource;
use mjfklib\Container\Env;
use mjfklib\Logger\Handler\FileHandler;
use mjfklib\Logger\Processor\ElapsedTimeProcessor;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\ProcessorInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class LoggerDefinitionSource extends DefinitionSource
{
    /**
     * @param Env $env
     * @return array<string,mixed>
     */
    protected function createDefinitions(Env $env): array
    {
        $definitions = [
            LoggerConfig::class => static::factory(
                [LoggerConfig::class, 'create']
            ),
            LoggerInterface::class => static::factory(
                [LoggerFactory::class, 'create'],
            ),
            HandlerInterface::class => static::get(FileHandler::class),
            ProcessorInterface::class => static::get(ElapsedTimeProcessor::class)
        ];

        $loggerAwareClasses = $env->classRepo->getClasses(LoggerAwareInterface::class);
        foreach ($loggerAwareClasses as $refClass) {
            if (isset($definitions[$refClass->getName()])) {
                continue;
            }

            $definitions[$refClass->getName()] = static::decorate(function ($previous, ContainerInterface $c) {
                if ($previous instanceof LoggerAwareInterface) {
                    $logger = $c->get(LoggerInterface::class);
                    if ($logger instanceof LoggerInterface) {
                        $previous->setLogger($logger);
                    }
                }
                return $previous;
            });
        }

        return $definitions;
    }
}
