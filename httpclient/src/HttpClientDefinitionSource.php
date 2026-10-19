<?php

declare(strict_types=1);

namespace mjfklib\HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use mjfklib\Container\DefinitionSource;
use mjfklib\Container\Env;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class HttpClientDefinitionSource extends DefinitionSource
{
    /**
     * @inheritdoc
     */
    protected function createDefinitions(Env $env): array
    {
        return [
            Client::class => static::autowire(null, [
                'config' => ['stream' => true]
            ]),
            ClientInterface::class               => static::get(Client::class),
            RequestFactoryInterface::class       => static::get(HttpFactory::class),
            ServerRequestFactoryInterface::class => static::get(HttpFactory::class),
            StreamFactoryInterface::class        => static::get(HttpFactory::class),
            UriFactoryInterface::class           => static::get(HttpFactory::class),
            UploadedFileFactoryInterface::class  => static::get(HttpFactory::class),
        ];
    }
}
