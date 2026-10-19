<?php

declare(strict_types=1);

namespace mjfklib\Console;

use DI\Container;
use mjfklib\Container\ContainerFactory;
use mjfklib\Container\DefinitionSource;
use mjfklib\Container\Env;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationInvoker
{
    /**
     * @param string|null $appDir
     * @param string|null $appNamespace
     * @param string|null $appName
     * @param string|null $appEnv
     */
    final public static function run(
        string|null $appDir = null,
        string|null $appNamespace = null,
        string|null $appName = null,
        string|null $appEnv = null,
    ): void {
        exit((new static())->invoke(
            $appDir,
            $appNamespace,
            $appName,
            $appEnv
        ));
    }


    final public function __construct()
    {
    }


    /**
     * @param string|null $appDir
     * @param string|null $appNamespace
     * @param string|null $appName
     * @param string|null $appEnv
     * @return int
     */
    public function invoke(
        string|null $appDir = null,
        string|null $appNamespace = null,
        string|null $appName = null,
        string|null $appEnv = null,
    ): int {
        $env = $this->createEnv(
            $appDir,
            $appNamespace,
            $appName,
            $appEnv
        );

        $container = $this->createContainer($env);

        chdir($env->appDir);

        $exitCode = $container->call([Application::class, 'run'], [
            'input' => DefinitionSource::get(InputInterface::class),
            'output' => DefinitionSource::get(OutputInterface::class)
        ]);

        return is_int($exitCode) ? $exitCode : 1;
    }


    /**
     * @return Env
     */
    protected function createEnv(
        string|null $appDir = null,
        string|null $appNamespace = null,
        string|null $appName = null,
        string|null $appEnv = null,
    ): Env {
        return new Env(
            $appDir,
            $appNamespace,
            $appName,
            $appEnv
        );
    }


    /**
     * @param Env $env
     * @return Container
     */
    protected function createContainer(Env $env): Container
    {
        return (new ContainerFactory($env))
            ->create([
                ConsoleDefinitionSource::class
            ]);
    }
}
