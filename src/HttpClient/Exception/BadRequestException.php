<?php

declare(strict_types=1);

namespace Cetloe\HttpClient\Exception;

class BadRequestException extends HttpClientErrorException
{
    protected string $defaultMessage = 'Bad Request';
}
