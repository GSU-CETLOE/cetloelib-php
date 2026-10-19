<?php

declare(strict_types=1);

namespace mjfklib\HttpClient;

use mjfklib\HttpClient\Exception\HttpException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

abstract class HttpAPIClient implements RequestFactoryInterface, ClientInterface
{
    /**
     * @param string $url
     * @param array<string,mixed> $params
     * @return string
     */
    public static function buildURL(
        string $url,
        array $params = []
    ): string {
        $params = array_filter($params, fn ($v) => $v !== null);
        return $url . ((count($params) > 0) ? '?' . http_build_query($params, "", null, PHP_QUERY_RFC3986) : '');
    }


    /**
     * @param RequestFactoryInterface $requestFactory
     * @param ClientInterface $client
     */
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly ClientInterface $client,
    ) {
    }


    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @return RequestInterface
     */
    public function createRequest(
        string $method,
        mixed $uri
    ): RequestInterface {
        return $this->requestFactory
            ->createRequest($method, $uri);
    }


    /**
     * @param RequestInterface $request
     * @param object|array<string,mixed> $params
     * @return RequestInterface
     */
    public function addRequestParams(
        RequestInterface $request,
        object|array $params
    ): RequestInterface {
        $request->getBody()->write(
            http_build_query(
                $params,
                "",
                null,
                PHP_QUERY_RFC3986
            )
        );

        return $request->withHeader(
            'Content-Type',
            'application/x-www-form-urlencoded'
        );
    }


    /**
     * @param RequestInterface $request
     * @param int|int[]|null $validCodes
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws HttpException
     */
    public function sendRequest(
        RequestInterface $request,
        int|array|null $validCodes = null
    ): ResponseInterface {
        if (is_int($validCodes)) {
            $validCodes = [$validCodes];
        }
        $response = $this->client->sendRequest($request);
        $statusCode = $response->getStatusCode();
        if (is_array($validCodes) && !in_array($statusCode, $validCodes, true)) {
            throw HttpException::create($request, $response);
        }
        return $response;
    }


    /**
     * @param ResponseInterface $response
     * @return mixed[]
     */
    public function getResponseValues(ResponseInterface $response): array
    {
        $responseValues = json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        if (!is_array($responseValues)) {
            throw new \RuntimeException('Invalid content in response');
        }
        return $responseValues;
    }
}
