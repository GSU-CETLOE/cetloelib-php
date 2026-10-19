<?php

declare(strict_types=1);

namespace mjfklib\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

class ApplicationFactory
{
    /**
     * @param string|null $appName
     * @param string|null $appVersion
     * @param CommandLoaderInterface $commandLoader
     * @return Application
     */
    public function create(
        string|null $appName,
        string|null $appVersion,
        CommandLoaderInterface $commandLoader
    ): Application {
        $application = new Application(
            $appName ?? 'UNKNOWN',
            $appVersion ?? 'UNKNOWN'
        );
        $application->setAutoExit(false);
        $application->setCatchExceptions(true);
        $application->setCommandLoader($commandLoader);
        return $application;
    }
}
