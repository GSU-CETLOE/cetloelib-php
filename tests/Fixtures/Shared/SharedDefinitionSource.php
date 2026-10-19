<?php

declare(strict_types=1);

namespace Cetloe\Tests\Fixtures\Shared;

use Cetloe\Container\DefinitionSource;
use Cetloe\Container\Env;

/**
 * Definition source that lives outside the fixture application and is only reachable through getSources().
 */
class SharedDefinitionSource extends DefinitionSource
{
    public const SHARED_ONLY = 'shared.only';


    /**
     * @inheritdoc
     */
    protected function createDefinitions(Env $env): array
    {
        return [
            'fixture.greeting' => 'hello from shared',
            self::SHARED_ONLY => 'shared value',
        ];
    }
}
