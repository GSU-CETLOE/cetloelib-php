<?php

declare(strict_types=1);

namespace mjfklib\Console\Output;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputFactory
{
    public const VERBOSITY_QUIET = OutputInterface::VERBOSITY_QUIET;
    public const VERBOSITY_DEBUG = OutputInterface::VERBOSITY_DEBUG;
    public const VERBOSITY_NORMAL = OutputInterface::VERBOSITY_NORMAL;
    public const VERBOSITY_VERBOSE = OutputInterface::VERBOSITY_VERBOSE;
    public const VERBOSITY_VERY_VERBOSE = OutputInterface::VERBOSITY_VERY_VERBOSE;


    /**
     * @param InputInterface $input
     * @return ConsoleOutput
     */
    public function create(InputInterface $input): ConsoleOutput
    {
        global $_ENV, $_SERVER;

        $verbosity = match (true) {
            (true === $input->hasParameterOption(['--quiet', '-q'], true)) => static::VERBOSITY_QUIET,
            ($input->hasParameterOption('-vvv', true))                     => static::VERBOSITY_DEBUG,
            ($input->hasParameterOption('--verbose=3', true))              => static::VERBOSITY_DEBUG,
            (3 === $input->getParameterOption('--verbose', false, true))   => static::VERBOSITY_DEBUG,
            ($input->hasParameterOption('-vv', true))                      => static::VERBOSITY_VERY_VERBOSE,
            ($input->hasParameterOption('--verbose=2', true))              => static::VERBOSITY_VERY_VERBOSE,
            (2 === $input->getParameterOption('--verbose', false, true))   => static::VERBOSITY_VERY_VERBOSE,
            ($input->hasParameterOption('-v', true))                       => static::VERBOSITY_VERBOSE,
            ($input->hasParameterOption('--verbose=1', true))              => static::VERBOSITY_VERBOSE,
            (1 === $input->getParameterOption('--verbose', false, true))   => static::VERBOSITY_VERBOSE,
            default => match ((int) getenv('SHELL_VERBOSITY')) {
                -1 => OutputInterface::VERBOSITY_QUIET,
                1  => OutputInterface::VERBOSITY_VERBOSE,
                2  => OutputInterface::VERBOSITY_VERY_VERBOSE,
                3  => OutputInterface::VERBOSITY_DEBUG,
                default => OutputInterface::VERBOSITY_NORMAL
            }
        };

        $shellVerbosity = match ($verbosity) {
            OutputInterface::VERBOSITY_QUIET        => -1,
            OutputInterface::VERBOSITY_VERBOSE      => 1,
            OutputInterface::VERBOSITY_VERY_VERBOSE => 2,
            OutputInterface::VERBOSITY_DEBUG        => 3,
            default                                 => 0
        };

        // decorated
        $decorated = null;
        if (true === $input->hasParameterOption(['--ansi'], true)) {
            $decorated = true;
        } elseif (true === $input->hasParameterOption(['--no-ansi'], true)) {
            $decorated = false;
        }

        if (\function_exists('putenv')) {
            @putenv('SHELL_VERBOSITY=' . $shellVerbosity);
        }
        $_ENV['SHELL_VERBOSITY'] = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;

        return new ConsoleOutput($verbosity, $decorated);
    }
}
