<?php

declare(strict_types=1);

namespace Cetloe\HttpClient\Exception;

class MovedPermanentlyException extends HttpRedirectionException
{
    protected string $defaultMessage = 'Moved Permanently';
}
