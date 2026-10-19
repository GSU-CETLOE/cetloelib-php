<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\App;

use Cetloe\Container\DefinitionSource;
use Cetloe\Container\Env;
use Cetloe\Tests\Fixtures\Shared\SharedDefinitionSource;

/**
 * Definition source discovered from the fixture application's own src directory.
 */
class FixtureDefinitionSource extends DefinitionSource
{
    public const GREETING = 'fixture.greeting';
    public const APP_ONLY = 'fixture.app-only';


    /**
     * @inheritdoc
     */
    protected function createDefinitions(Env $env): array
    {
        return [
            self::GREETING => 'hello from app',
            self::APP_ONLY => $env->appName,
        ];
    }


    /**
     * @inheritdoc
     */
    public function getSources(): array
    {
        return [SharedDefinitionSource::class];
    }
}
