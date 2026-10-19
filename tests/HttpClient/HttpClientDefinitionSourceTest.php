<?php

declare(strict_types=1);

namespace Cetloe\Tests\HttpClient;

use Cetloe\Container\ContainerFactory;
use Cetloe\HttpClient\HttpClientDefinitionSource;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class HttpClientDefinitionSourceTest extends EnvironmentTestCase
{
    public function testPsrInterfacesResolveToGuzzleImplementations(): void
    {
        $container = (new ContainerFactory(FixtureApp::createEnv()))->create([HttpClientDefinitionSource::class]);

        $client = $container->get(ClientInterface::class);
        self::assertInstanceOf(Client::class, $client);
        // getConfig() is deprecated in Guzzle 7 and gone in 8, but it is the only way to read the client
        // configuration, and the 'stream' option is the one thing this definition source sets on the client.
        // @phpstan-ignore method.deprecated
        self::assertTrue($client->getConfig('stream'));

        $factory = $container->get(RequestFactoryInterface::class);
        self::assertInstanceOf(HttpFactory::class, $factory);
        self::assertSame($factory, $container->get(ServerRequestFactoryInterface::class));
        self::assertSame($factory, $container->get(StreamFactoryInterface::class));
        self::assertSame($factory, $container->get(UriFactoryInterface::class));
        self::assertSame($factory, $container->get(UploadedFileFactoryInterface::class));
    }
}
