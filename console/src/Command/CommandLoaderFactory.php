<?php

declare(strict_types=1);

namespace mjfklib\Console\Command;

use mjfklib\Container\ClassRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class CommandLoaderFactory
{
    /**
     * @param ClassRepository $classRepo
     * @param ContainerInterface $container
     * @return CommandLoaderInterface
     */
    public function create(
        ClassRepository $classRepo,
        ContainerInterface $container
    ): CommandLoaderInterface {
        $commandMap = [];

        $commands = $classRepo->getClasses(Command::class);
        foreach ($commands as $command) {
            $refAttr = ClassRepository::getAttribute($command, AsCommand::class);
            if ($refAttr !== null) {
                $commandMap[$refAttr->name] = $command->getName();
            }
        }

        return new ContainerCommandLoader($container, $commandMap);
    }
}
