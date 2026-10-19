<?php

declare(strict_types=1);

namespace Cetloe\Tests\Container;

use Cetloe\Container\DefinitionSource;
use Cetloe\Container\Env;
use Cetloe\Tests\Fixtures\Container\Counter;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

final class DefinitionSourceTest extends EnvironmentTestCase
{
    public function testGetReferencesTheNamedEntry(): void
    {
        self::assertSame('entry', DefinitionSource::get('entry')->getTargetEntryName());
    }


    public function testDefaultSourceListIsEmpty(): void
    {
        $source = $this->createSource(fn (Env $env): array => []);

        self::assertSame([], $source->getSources());
    }


    public function testDefinitionsResolveThroughAContainer(): void
    {
        $source = $this->createSource(fn (Env $env): array => [
            'config.name' => $env->appName,
            'counter' => DefinitionSource::autowire(Counter::class, ['start' => 5]),
            'counter.created' => DefinitionSource::create(Counter::class),
            'counter.factory' => DefinitionSource::factory(
                fn (ContainerInterface $c, int $start): Counter => new Counter($start),
                ['start' => 7]
            ),
            'counter.alias' => DefinitionSource::get('counter'),
            Counter::class => DefinitionSource::decorate(
                fn (Counter $previous): Counter => new Counter($previous->start + 100)
            ),
        ]);

        $container = (new ContainerBuilder())->useAutowiring(true)->addDefinitions($source)->build();

        self::assertSame(FixtureApp::NAME, $container->get('config.name'));
        self::assertSame(5, $this->counter($container, 'counter')->start);
        self::assertSame(0, $this->counter($container, 'counter.created')->start);
        self::assertSame(7, $this->counter($container, 'counter.factory')->start);
        self::assertSame($container->get('counter'), $container->get('counter.alias'));
        self::assertSame(100, self::resolve($container, Counter::class)->start);
    }


    /**
     * @param ContainerInterface $container
     * @param string $entry
     * @return Counter
     */
    private function counter(ContainerInterface $container, string $entry): Counter
    {
        $counter = $container->get($entry);
        self::assertInstanceOf(Counter::class, $counter);
        return $counter;
    }


    /**
     * @param callable(Env): array<string,mixed> $definitions
     * @return DefinitionSource
     */
    private function createSource(callable $definitions): DefinitionSource
    {
        return new class (FixtureApp::createEnv(), $definitions) extends DefinitionSource {
            /** @var callable(Env): array<string,mixed> */
            private $definitions;


            /**
             * @param Env $env
             * @param callable(Env): array<string,mixed> $definitions
             */
            public function __construct(Env $env, callable $definitions)
            {
                $this->definitions = $definitions;
                parent::__construct($env);
            }


            /**
             * @inheritdoc
             */
            protected function createDefinitions(Env $env): array
            {
                return ($this->definitions)($env);
            }
        };
    }
}
