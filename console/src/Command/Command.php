<?php

declare(strict_types=1);

namespace mjfklib\Console\Command;

use mjfklib\Logger\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand implements LoggerAwareInterface
{
    use LoggerAwareTrait;


    /**
     * @param bool $logStartFinish
     * @param bool $logError
     */
    public function __construct(
        protected bool $logStartFinish = true,
        protected bool $logError = true
    ) {
        parent::__construct();
    }


    /**
     * @inheritdoc
     */
    public function run(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $exitCode = null;

        try {
            $this->logCommandStart($input);

            $exitCode = parent::run($input, $output);

            return $exitCode;
        } catch (\Throwable $t) {
            $this->logError($input, $t);
            throw $t;
        } finally {
            $this->logCommandFinish($input, $exitCode ?? static::FAILURE);
        }
    }


    /**
     * @param InputInterface $input
     * @return void
     */
    protected function logCommandStart(InputInterface $input): void
    {
        if ($this->logStartFinish) {
            $this->logger?->info(sprintf("Started %s", $input->__toString()));
        }
    }


    /**
     * @param InputInterface $input
     * @param \Throwable $t
     * @param string[]|null $seen
     * @return void
     */
    protected function logError(
        InputInterface $input,
        \Throwable $t,
        ?array $seen = null
    ): void {
        if (!$this->logError) {
            return;
        }

        $result = [];
        $starter = is_array($seen)
            ? 'Caused by: '
            : sprintf("Error in %s - ", $input->__toString());
        if ($seen === null) {
            $seen = [];
        }

        $this->logger?->error(sprintf(
            '%s%s: %s',
            $starter,
            get_class($t),
            $t->getMessage()
        ));

        $file = $t->getFile();
        $line = $t->getLine();
        /** @var array<int,array<string,string>> $trace */
        $trace = $t->getTrace();
        $prev = $t->getPrevious();

        do {
            $current = "{$file}:{$line}";
            if (in_array($current, $seen, true)) {
                $result[] = sprintf(' ... %d more', count($trace) + 1);
                break;
            } else {
                $seen[] = $current;
            }

            $traceFile =  $trace[0]['file'] ?? 'Unknown Source';
            $traceLine = intval(isset($trace[0]['file']) ? ($trace[0]['line'] ?? 0) : 0);
            if ($traceLine < 1) {
                $traceLine = null;
            }
            $traceClass = $trace[0]['class'] ?? null;
            $traceFunction = $trace[0]['function'] ?? null;

            $this->logger?->error(sprintf(
                ' at %s%s%s(%s%s%s)',
                str_replace('\\', '.', ($traceClass ?? '')),
                is_string($traceClass) && is_string($traceFunction) ? '.' : '',
                str_replace('\\', '.', ($traceFunction ?? '(main)')),
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line
            ));

            $file = $traceFile;
            $line = $traceLine;
            array_shift($trace);
        } while (count($trace) > 0);

        if ($prev !== null) {
            $this->logError($input, $prev, $seen);
        }
    }


    /**
     * @param InputInterface $input
     * @param int $exitCode
     * @return void
     */
    protected function logCommandFinish(
        InputInterface $input,
        int $exitCode
    ): void {
        if ($this->logStartFinish) {
            $this->logger?->info(sprintf(
                "Finished %s, code => %d",
                $input->__toString(),
                $exitCode
            ));
        }
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        return self::SUCCESS;
    }


    /**
     * @param InputInterface $input
     * @param string $name
     * @param string[] $validArgs
     * @return array<int,string>
     */
    protected function getArgumentArray(
        InputInterface $input,
        string $name,
        array $validArgs = []
    ): array {
        $argArray = $input->getArgument($name);
        $argArray = is_array($argArray) ? array_map(
            fn ($v) => strval($v),
            array_filter(
                $argArray,
                fn ($v) => is_scalar($v)
            )
        ) : [];

        if (count($validArgs) > 0 && count($argArray) > 0) {
            $diff = array_diff($argArray, $validArgs);
            if (count($diff) > 0) {
                throw new \RuntimeException("Invalid argument values: " . implode(",", $diff));
            }
        }

        return $argArray;
    }
}
