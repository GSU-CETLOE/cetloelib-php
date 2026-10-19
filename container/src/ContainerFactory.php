<?php

declare(strict_types=1);

namespace mjfklib\Container;

use DI\Container;
use DI\ContainerBuilder;

class ContainerFactory
{
    /**
     * @param Env $env
     */
    public function __construct(protected Env $env)
    {
    }


    /**
     * @param class-string[] $globalRefs
     * @return Container
     */
    public function create(array $globalRefs = []): Container
    {
        $container = (new ContainerBuilder())
            ->useAutowiring(true)
            ->useAttributes(true)
            ->addDefinitions(...$this->getDefinitionSources($globalRefs))
            ->build();

        $container->set(Env::class, $this->env);
        $container->set(ClassRepository::class, $this->env->classRepo);

        return $container;
    }


    /**
     * @param class-string[] $globalRefs
     * @return array<string, DefinitionSource>
     */
    protected function getDefinitionSources(array $globalRefs): array
    {
        /** @var array<string, DefinitionSource> $sources */
        $sources = [];

        /** @var \ReflectionClass<DefinitionSource>[] $queue */
        $queue = [
            ...array_values($this->env->classRepo->getClasses(DefinitionSource::class)),
            ...array_values(ClassRepository::getReflectionClasses(
                $globalRefs,
                DefinitionSource::class
            )),
        ];

        for ($sourceClass = array_shift($queue); $sourceClass !== null; $sourceClass = array_shift($queue)) {
            if (isset($sources[$sourceClass->getName()])) {
                continue;
            }

            $sources[$sourceClass->getName()] = $source = $sourceClass->newInstance($this->env);

            $sourceRefs = $source->getSources();
            foreach ($sourceRefs as $sourceRef) {
                if (!class_exists($sourceRef) || isset($sources[$sourceRef])) {
                    continue;
                }

                /** @var \ReflectionClass<DefinitionSource> $sourceRefClass */
                $sourceRefClass = new \ReflectionClass($sourceRef);
                if ($sourceRefClass->isInstantiable()) {
                    $queue[] = $sourceRefClass;
                }
            }
        }

        return array_reverse($sources, true);
    }
}
