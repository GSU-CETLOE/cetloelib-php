<?php

declare(strict_types=1);

namespace Cetloe\Tests\HttpClient;

use Cetloe\HttpClient\Exception\NotFoundException;
use Cetloe\HttpClient\HttpAPIClient;
use Cetloe\Tests\Fixtures\Http\FixtureApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class HttpAPIClientTest extends TestCase
{
    /**
     * Builds the client over a Guzzle handler that replays the given responses instead of touching the network.
     *
     * @param Response ...$responses
     * @return FixtureApiClient
     */
    private function createClient(Response ...$responses): FixtureApiClient
    {
        $handler = HandlerStack::create(new MockHandler(array_values($responses)));
        return new FixtureApiClient(new HttpFactory(), new Client(['handler' => $handler]));
    }


    public function testBuildUrlAppendsRfc3986EncodedQueryAndDropsNulls(): void
    {
        self::assertSame('https://x.test/p', HttpAPIClient::buildURL('https://x.test/p'));
        self::assertSame('https://x.test/p', HttpAPIClient::buildURL('https://x.test/p', ['a' => null]));
        self::assertSame(
            'https://x.test/p?a=1&b=two%20words&c=0',
            HttpAPIClient::buildURL('https://x.test/p', ['a' => 1, 'b' => 'two words', 'c' => 0, 'd' => null])
        );
    }


    public function testCreateRequestDelegatesToTheRequestFactory(): void
    {
        $request = $this->createClient()->createRequest('POST', 'https://x.test/items');

        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://x.test/items', (string)$request->getUri());
    }


    public function testAddRequestParamsWritesFormBodyAndContentType(): void
    {
        $client = $this->createClient();
        $request = $client->addRequestParams(
            $client->createRequest('POST', 'https://x.test/items'),
            ['name' => 'a b', 'n' => 1]
        );

        self::assertSame('name=a%20b&n=1', (string)$request->getBody());
        self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));

        $fromObject = $client->addRequestParams(
            $client->createRequest('POST', 'https://x.test/items'),
            (object)['k' => 'v']
        );
        self::assertSame('k=v', (string)$fromObject->getBody());
    }


    public function testSendRequestReturnsAnyResponseWhenNoCodesAreRequired(): void
    {
        $client = $this->createClient(new Response(404));

        self::assertSame(404, $client->sendRequest(new Request('GET', 'https://x.test/'))->getStatusCode());
    }


    public function testSendRequestAcceptsListedCodes(): void
    {
        $client = $this->createClient(new Response(201), new Response(204));

        self::assertSame(201, $client->sendRequest(new Request('GET', '/'), 201)->getStatusCode());
        self::assertSame(204, $client->sendRequest(new Request('GET', '/'), [200, 204])->getStatusCode());
    }


    public function testSendRequestThrowsTheTypedExceptionForUnexpectedCodes(): void
    {
        $client = $this->createClient(new Response(404));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('404 Not Found');
        $client->sendRequest(new Request('GET', '/'), 200);
    }


    public function testGetResponseValuesDecodesJsonObjects(): void
    {
        $values = $this->createClient()->getResponseValues(new Response(200, [], '{"items":[1,2]}'));

        self::assertSame(['items' => [1, 2]], $values);
    }


    public function testGetResponseValuesRejectsNonArrayJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid content in response');
        $this->createClient()->getResponseValues(new Response(200, [], '"just a string"'));
    }


    public function testGetResponseValuesRejectsMalformedJson(): void
    {
        $this->expectException(\JsonException::class);
        $this->createClient()->getResponseValues(new Response(200, [], '{oops'));
    }
}
