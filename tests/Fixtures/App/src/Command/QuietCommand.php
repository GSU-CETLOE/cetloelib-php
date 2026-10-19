<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\App\Command;

use Cetloe\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Succeeds with start/finish logging switched off. Relies on the base execute() returning SUCCESS.
 */
#[AsCommand(name: self::NAME)]
class QuietCommand extends Command
{
    public const NAME = 'fixture:quiet';


    public function __construct()
    {
        parent::__construct(logStartFinish: false, logError: false);
    }
}
