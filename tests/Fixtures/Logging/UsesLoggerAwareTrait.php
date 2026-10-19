<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\Logging;

use Cetloe\Logger\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

/**
 * Exposes the trait's protected helpers so they can be tested directly.
 */
final class UsesLoggerAwareTrait implements LoggerAwareInterface
{
    use LoggerAwareTrait;


    /**
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger(): ?\Psr\Log\LoggerInterface
    {
        return $this->logger;
    }


    /**
     * @param float $start
     * @return string
     */
    public function elapsed(float $start): string
    {
        return $this->getElapsedTime($start);
    }


    /**
     * @param mixed[] $results
     * @return string
     */
    public function format(array $results): string
    {
        return $this->formatLogResults($results);
    }
}
