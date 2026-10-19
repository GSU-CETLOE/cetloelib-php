<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\App\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Plain service that wants a logger injected by the container.
 */
class Greeter implements LoggerAwareInterface
{
    use LoggerAwareTrait;


    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}
