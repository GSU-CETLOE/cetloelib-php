<?php

declare(strict_types=1);

namespace Cetloe\HttpClient\Exception;

class FoundException extends HttpRedirectionException
{
    protected string $defaultMessage = 'Found';
}
