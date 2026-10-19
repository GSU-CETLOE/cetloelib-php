<?php

declare(strict_types=1);

namespace Cetloe\Tests\Support;

use Cetloe\Container\Env;

/**
 * Locates the fixture application under tests/Fixtures/App and builds an Env for it.
 */
final class FixtureApp
{
    public const NAMESPACE = 'Cetloe\Tests\Fixtures\App';
    public const NAME = 'fixture-app';


    /**
     * @return string absolute path of the fixture application directory
     */
    public static function dir(): string
    {
        return dirname(__DIR__) . '/Fixtures/App';
    }


    /**
     * @param array<string,string> $extraEnv variables placed in $_ENV before the Env is built
     * @return Env
     */
    public static function createEnv(array $extraEnv = []): Env
    {
        foreach ($extraEnv as $name => $value) {
            $_ENV[$name] = $value;
        }

        return new Env(self::dir(), self::NAMESPACE, self::NAME, 'test');
    }
}
