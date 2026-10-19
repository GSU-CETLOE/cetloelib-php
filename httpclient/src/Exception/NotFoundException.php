<?php

declare(strict_types=1);

namespace mjfklib\HttpClient\Exception;

class NotFoundException extends HttpException
{
    protected string $defaultMessage = 'Not Found';
}
