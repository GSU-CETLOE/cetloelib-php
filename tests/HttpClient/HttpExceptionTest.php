<?php

declare(strict_types=1);

namespace Cetloe\Tests\HttpClient;

use Cetloe\HttpClient\Exception\BadRequestException;
use Cetloe\HttpClient\Exception\ForbiddenException;
use Cetloe\HttpClient\Exception\FoundException;
use Cetloe\HttpClient\Exception\HttpClientErrorException;
use Cetloe\HttpClient\Exception\HttpException;
use Cetloe\HttpClient\Exception\HttpInfoException;
use Cetloe\HttpClient\Exception\HttpRedirectionException;
use Cetloe\HttpClient\Exception\HttpServerErrorException;
use Cetloe\HttpClient\Exception\HttpSuccessException;
use Cetloe\HttpClient\Exception\MovedPermanentlyException;
use Cetloe\HttpClient\Exception\NotFoundException;
use Cetloe\HttpClient\Exception\TooManyRequestsException;
use Cetloe\HttpClient\Exception\UnauthorizedException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HttpExceptionTest extends TestCase
{
    /**
     * @return array<int|string,array{0:int,1:class-string<HttpException>,2:string}>
     */
    public static function statusCodes(): array
    {
        return [
            '100' => [100, HttpInfoException::class, '100'],
            '200' => [200, HttpSuccessException::class, '200'],
            '301' => [301, MovedPermanentlyException::class, '301 Moved Permanently'],
            '302' => [302, FoundException::class, '302 Found'],
            '307' => [307, HttpRedirectionException::class, '307'],
            '400' => [400, BadRequestException::class, '400 Bad Request'],
            '401' => [401, UnauthorizedException::class, '401 Unauthorized'],
            '403' => [403, ForbiddenException::class, '403 Forbidden'],
            '404' => [404, NotFoundException::class, '404 Not Found'],
            '429' => [429, TooManyRequestsException::class, '429 Too Many Requests'],
            '418' => [418, HttpClientErrorException::class, '418'],
            '500' => [500, HttpServerErrorException::class, '500'],
            '503' => [503, HttpServerErrorException::class, '503'],
        ];
    }


    /**
     * @param int $code
     * @param class-string<HttpException> $expectedClass
     * @param string $expectedMessage
     */
    #[DataProvider('statusCodes')]
    public function testCreatePicksTheExceptionForTheStatusCode(
        int $code,
        string $expectedClass,
        string $expectedMessage
    ): void {
        $request = new Request('GET', 'https://example.test/');
        $response = new Response($code);

        $exception = HttpException::create($request, $response);

        self::assertSame($expectedClass, get_class($exception));
        self::assertSame($expectedMessage, $exception->getMessage());
        self::assertSame($code, $exception->getCode());
        self::assertSame($request, $exception->request);
        self::assertSame($response, $exception->response);
    }


    public function testExplicitMessageAndPreviousArePreserved(): void
    {
        $previous = new \LogicException('cause');
        $exception = HttpException::create(new Request('GET', '/'), new Response(404), 'custom', $previous);

        self::assertSame('custom', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }
}
