<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\App\Command;

use Cetloe\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Greets each name passed as an argument. Only the names in VALID_NAMES are accepted.
 */
#[AsCommand(name: self::NAME)]
class HelloCommand extends Command
{
    public const NAME = 'fixture:hello';
    public const VALID_NAMES = ['alice', 'bob'];


    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->addArgument('names', InputArgument::IS_ARRAY, 'Who to greet');
    }


    /**
     * @inheritdoc
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        foreach ($this->getArgumentArray($input, 'names', self::VALID_NAMES) as $name) {
            $output->writeln("Hello {$name}");
        }

        return self::SUCCESS;
    }
}
