<?php

declare(strict_types=1);

namespace mjfklib\HttpClient\Exception;

class UnauthorizedException extends HttpException
{
    protected string $defaultMessage = 'Unauthorized';
}
