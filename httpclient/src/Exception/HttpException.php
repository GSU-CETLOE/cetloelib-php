<?php

declare(strict_types=1);

namespace mjfklib\HttpClient\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpException extends \RuntimeException implements ClientExceptionInterface
{
    public static function create(
        RequestInterface $request,
        ResponseInterface $response,
        ?string $message = null,
        \Throwable|null $previous = null
    ): self {
        $code = $response->getStatusCode();

        return match (true) {
            ($code >= 100 && $code < 200) => match ($code) {
                default => new HttpInfoException($request, $response, $message, $previous)
            },
            ($code >= 200 && $code < 300) => match ($code) {
                default => new HttpSuccessException($request, $response, $message, $previous)
            },
            ($code >= 300 && $code < 400) => match ($code) {
                301 => new FoundException($request, $response, $message, $previous),
                302 => new HttpRedirectionException($request, $response, $message, $previous),
                default => new HttpRedirectionException($request, $response, $message, $previous)
            },
            ($code >= 400 && $code < 500) => match ($code) {
                400 => new BadRequestException($request, $response, $message, $previous),
                401 => new UnauthorizedException($request, $response, $message, $previous),
                403 => new ForbiddenException($request, $response, $message, $previous),
                404 => new NotFoundException($request, $response, $message, $previous),
                429 => new TooManyRequestsException($request, $response, $message, $previous),
                default => new HttpClientErrorException($request, $response, $message, $previous)
            },
            ($code >= 500) => match ($code) {
                default => new HttpServerErrorException($request, $response, $message, $previous),
            },
            default => new self($request, $response, $message, $previous)
        };
    }


    protected string $defaultMessage = '';


    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string|null $message
     * @param \Throwable|null $previous
     */
    public function __construct(
        public RequestInterface $request,
        public ResponseInterface $response,
        ?string $message = null,
        \Throwable|null $previous = null
    ) {
        $message ??= $response->getStatusCode()
            . (strlen($this->defaultMessage) > 0 ? ' ' . $this->defaultMessage : '');

        parent::__construct(
            $message,
            $response->getStatusCode(),
            $previous
        );
    }
}
