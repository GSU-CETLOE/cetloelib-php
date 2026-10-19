<?php

declare(strict_types=1);

namespace mjfklib\HttpClient\Exception;

class ForbiddenException extends HttpException
{
    protected string $defaultMessage = 'Forbidden';
}
