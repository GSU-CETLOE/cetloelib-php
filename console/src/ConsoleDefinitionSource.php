<?php

declare(strict_types=1);

namespace mjfklib\Console;

use mjfklib\Console\Command\CommandLoaderFactory;
use mjfklib\Console\Output\ConsoleOutputFactory;
use mjfklib\Container\DefinitionSource;
use mjfklib\Container\Env;
use mjfklib\Logger\LoggerDefinitionSource;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleDefinitionSource extends DefinitionSource
{
    /**
     * @param Env $env
     * @return array<string,mixed>
     */
    protected function createDefinitions(Env $env): array
    {
        return [
            InputInterface::class => static::get(ArgvInput::class),
            OutputInterface::class => static::factory(
                [ConsoleOutputFactory::class, 'create'],
            ),
            Application::class => static::factory(
                [ApplicationFactory::class, 'create'],
                [
                    'appName' => $env['APP_NAME'] ?? null,
                    'appVersion' => $env['APP_VERSION'] ?? null
                ]
            ),
            CommandLoaderInterface::class => static::factory(
                [CommandLoaderFactory::class, 'create']
            ),
        ];
    }


    /**
     * @inheritdoc
     */
    public function getSources(): array
    {
        return [
            LoggerDefinitionSource::class
        ];
    }
}
