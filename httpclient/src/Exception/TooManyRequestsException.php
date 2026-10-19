<?php

declare(strict_types=1);

namespace mjfklib\HttpClient\Exception;

class TooManyRequestsException extends HttpException
{
    protected string $defaultMessage = 'Too Many Requests';
}
