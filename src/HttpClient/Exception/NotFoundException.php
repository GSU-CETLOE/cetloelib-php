<?php

declare(strict_types=1);

namespace Cetloe\HttpClient\Exception;

class NotFoundException extends HttpException
{
    protected string $defaultMessage = 'Not Found';
}
