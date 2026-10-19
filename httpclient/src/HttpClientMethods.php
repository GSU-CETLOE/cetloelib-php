<?php

declare(strict_types=1);

namespace mjfklib\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientMethods
{
    /**
     * @param RequestInterface $request
     * @param array<int,string[]> $cookies
     * @return RequestInterface
     */
    public static function setRequestCookies(
        RequestInterface $request,
        array $cookies
    ): RequestInterface {
        return $request->withHeader(
            'Cookie',
            implode('; ', array_map(fn ($v) => $v['name'] . "=" . urlencode($v['value']), $cookies))
        );
    }


    /**
     * @param ResponseInterface $response
     * @return array<int,string[]>
     */
    public static function getResponseCookies(ResponseInterface $response): array
    {
        return array_map(
            function ($props) {
                $value = array_shift($props);
                array_unshift(
                    $props,
                    ['name', $value[0]],
                    ['value', urldecode($value[1])]
                );
                return array_column(
                    $props,
                    1,
                    0
                );
            },
            array_map(
                fn ($v) => array_map(
                    fn ($v) => array_map(
                        fn ($v) => trim($v),
                        explode('=', $v)
                    ),
                    explode(';', $v)
                ),
                $response->getHeader('Set-Cookie')
            )
        );
    }


    /**
     * @param ResponseInterface $response
     * @param string $outputFilePath
     * @param int $chunkSize
     * @return int
     */
    public static function writeResponseToFile(
        ResponseInterface $response,
        string $outputFilePath,
        int $chunkSize = 10485760
    ): int {
        $totalBytes = 0;

        try {
            $fp = fopen($outputFilePath, 'w');
            if (!is_resource($fp)) {
                throw new \RuntimeException();
            }

            $body = $response->getBody();
            while (!$body->eof()) {
                $chunkBytes = fwrite($fp, $body->read($chunkSize));
                if ($chunkBytes === false) {
                    throw new \RuntimeException();
                }
                $totalBytes += $chunkBytes;
            }
        } finally {
            if (is_resource($fp ?? null)) {
                fclose($fp);
            }
        }

        return $totalBytes;
    }
}
