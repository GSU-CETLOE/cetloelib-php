<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\App\Command;

use Cetloe\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Always throws, with a nested cause, so error logging can be exercised.
 */
#[AsCommand(name: self::NAME)]
class FailCommand extends Command
{
    public const NAME = 'fixture:fail';
    public const MESSAGE = 'fixture failure';
    public const CAUSE = 'root cause';


    /**
     * @inheritdoc
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        throw new \RuntimeException(self::MESSAGE, 0, new \LogicException(self::CAUSE));
    }
}
