<?php

declare(strict_types=1);

namespace mjfklib\HttpClient\Exception;

class FoundException extends HttpRedirectionException
{
    protected string $defaultMessage = 'Found';
}
