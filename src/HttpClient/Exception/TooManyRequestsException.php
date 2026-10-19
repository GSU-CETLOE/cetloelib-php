<?php

declare(strict_types=1);

namespace Cetloe\HttpClient\Exception;

class TooManyRequestsException extends HttpException
{
    protected string $defaultMessage = 'Too Many Requests';
}
