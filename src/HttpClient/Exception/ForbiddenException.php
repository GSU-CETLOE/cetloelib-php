<?php

declare(strict_types=1);

namespace Cetloe\HttpClient\Exception;

class ForbiddenException extends HttpException
{
    protected string $defaultMessage = 'Forbidden';
}
