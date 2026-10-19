<?php

declare(strict_types=1);

namespace Cetloe\Tests\HttpClient;

use Cetloe\HttpClient\HttpClientMethods;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\TempDir;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class HttpClientMethodsTest extends EnvironmentTestCase
{
    public function testSetRequestCookiesBuildsAnEncodedCookieHeader(): void
    {
        $request = HttpClientMethods::setRequestCookies(new Request('GET', '/'), [
            ['name' => 'session', 'value' => 'a b'],
            ['name' => 'theme', 'value' => 'dark'],
        ]);

        self::assertSame('session=a+b; theme=dark', $request->getHeaderLine('Cookie'));
    }


    public function testGetResponseCookiesParsesEachSetCookieHeader(): void
    {
        $response = new Response(200, [
            'Set-Cookie' => [
                'session=a%20b; Path=/; HttpOnly',
                'theme=dark; Max-Age=3600',
            ],
        ]);

        self::assertSame(
            [
                ['name' => 'session', 'value' => 'a b', 'Path' => '/'],
                ['name' => 'theme', 'value' => 'dark', 'Max-Age' => '3600'],
            ],
            HttpClientMethods::getResponseCookies($response)
        );
    }


    public function testGetResponseCookiesIsEmptyWithoutHeaders(): void
    {
        self::assertSame([], HttpClientMethods::getResponseCookies(new Response()));
    }


    public function testWriteResponseToFileStreamsTheBodyInChunks(): void
    {
        $dir = TempDir::create();
        try {
            $path = $dir . '/download.bin';
            $body = str_repeat('x', 25);

            $bytes = HttpClientMethods::writeResponseToFile(new Response(200, [], $body), $path, 10);

            self::assertSame(25, $bytes);
            self::assertSame($body, file_get_contents($path));
        } finally {
            TempDir::remove($dir);
        }
    }


    public function testWriteResponseToFileReportsUnwritablePath(): void
    {
        $warnings = $this->captureWarnings(function (): void {
            $this->expectException(\RuntimeException::class);
            HttpClientMethods::writeResponseToFile(new Response(200, [], 'x'), '/nonexistent-dir/file.bin');
        });

        self::assertCount(1, $warnings);
        self::assertStringContainsString('Failed to open stream', $warnings[0]);
    }
}
