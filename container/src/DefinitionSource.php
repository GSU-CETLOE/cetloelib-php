<?php

declare(strict_types=1);

namespace mjfklib\Container;

use DI\Definition\Helper\AutowireDefinitionHelper;
use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Definition\Helper\FactoryDefinitionHelper;
use DI\Definition\Reference;
use DI\Definition\Source\AttributeBasedAutowiring;
use DI\Definition\Source\DefinitionArray;

use function DI\autowire;
use function DI\create;
use function DI\decorate;
use function DI\factory;
use function DI\get;

abstract class DefinitionSource extends DefinitionArray
{
    /**
     * @param Env $env
     */
    public function __construct(Env $env)
    {
        parent::__construct(
            $this->createDefinitions($env),
            new AttributeBasedAutowiring()
        );
    }


    /**
     * @param Env $env
     * @return array<string,mixed>
     */
    abstract protected function createDefinitions(Env $env): array;



    /**
     * @return class-string<DefinitionSource>[]
     */
    public function getSources(): array
    {
        return [];
    }


    /**
     * Helper for autowiring an object.
     *
     * @param ?string $className Class name of the object. If null, the name of the entry (in the container) will be
     * used as class name.
     * @param mixed[] $params Defines a value for one or more arguments of the constructor
     * @return AutowireDefinitionHelper
     */
    public static function autowire(
        ?string $className = null,
        array $params = []
    ): AutowireDefinitionHelper {
        $_autowire = autowire($className);
        foreach ($params as $name => $value) {
            $_autowire->constructorParameter($name, $value);
        }
        return $_autowire;
    }


    /**
     * Helper for defining an object.
     *
     * @param ?string $className
     * Class name of the object. If null, the name of the entry (in the container) will be used as class name.
     *
     * @return CreateDefinitionHelper
     */
    public static function create(?string $className = null): CreateDefinitionHelper
    {
        return create($className);
    }


    /**
     * Decorate the previous definition using a callable.
     *
     * @param callable $callable
     * The callable takes the decorated object as first parameter and the container as second.
     */
    public static function decorate(callable $callable): FactoryDefinitionHelper
    {
        return decorate($callable);
    }


    /**
     * Helper for defining a container entry using a factory function/callable.
     *
     * @param callable|array<int,string>|string $factory
     * The factory is a callable that takes the container as parameter and returns the value to register in the
     * container.
     *
     * @param array<string,mixed> $params
     * @return FactoryDefinitionHelper
     */
    public static function factory(
        callable|array|string $factory,
        array $params = []
    ): FactoryDefinitionHelper {
        $_factory = factory($factory);
        foreach ($params as $name => $value) {
            $_factory->parameter($name, $value);
        }
        return $_factory;
    }


    /**
     * Helper for referencing another container entry in an object definition.
     *
     * @param string $entryName
     * @return Reference
     */
    public static function get(string $entryName): Reference
    {
        return get($entryName);
    }
}
