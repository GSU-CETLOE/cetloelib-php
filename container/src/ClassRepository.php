<?php

declare(strict_types=1);

namespace mjfklib\Container;

class ClassRepository
{
    /**
     * @template T of object
     * @param string[] $classes
     * @param class-string<T> $filterClassName
     * @return array<string,\ReflectionClass<T>>
     */
    public static function getReflectionClasses(
        array $classes,
        string $filterClassName
    ): array {
        $refClasses = [];

        $filterClass = new \ReflectionClass($filterClassName);

        if ($filterClass->isInterface()) {
            foreach ($classes as $className) {
                if (!class_exists($className)) {
                    continue;
                }

                $refClass = new \ReflectionClass($className);
                if ($refClass->isInstantiable() && $refClass->implementsInterface($filterClass)) {
                    /** @var \ReflectionClass<T> $refClass */
                    $refClasses[$refClass->getName()] = $refClass;
                }
            }
        } else {
            foreach ($classes as $className) {
                if (!class_exists($className)) {
                    continue;
                }

                $refClass = new \ReflectionClass($className);
                if ($refClass->isInstantiable() && $refClass->isSubclassOf($filterClass)) {
                    /** @var \ReflectionClass<T> $refClass */
                    $refClasses[$refClass->getName()] = $refClass;
                }
            }
        }

        return $refClasses;
    }


    /**
     * @var array<string,\ReflectionClass<object>>
     */
    public readonly array $classes;


    /**
     * @param string $appDir
     * @param string $appNamespace
     */
    public function __construct(
        string $appDir,
        string $appNamespace
    ) {
        $srcDir = $appDir . '/src';
        if (!is_dir($srcDir)) {
            throw new \RuntimeException("Not a directory: {$srcDir}");
        }
        if ($appNamespace === '') {
            throw new \RuntimeException("Missing application namespace");
        }

        $this->classes = $this->findClasses(
            $srcDir,
            $appNamespace
        );
    }


    /**
     * @param string $srcDir
     * @param string $appNamespace
     * @return array<string,\ReflectionClass<object>>
     */
    protected function findClasses(
        string $srcDir,
        string $appNamespace
    ): array {
        $classes = [];

        /** @var \Iterator<int|string,string[]> */
        $files = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($srcDir)
            ),
            '/^.+\.php$/i',
            \RecursiveRegexIterator::GET_MATCH
        );

        foreach ($files as $file) {
            $fileName = array_shift($file) ?? '';
            $className = $appNamespace . str_replace('/', '\\', substr($fileName, strlen($srcDir), -4));
            if (
                class_exists($className)
                || interface_exists($className)
                || trait_exists($className)
                || enum_exists($className)
            ) {
                $classes[$className] = new \ReflectionClass($className);
            } else {
                throw new \ReflectionException("Class not found: {$className}");
            }
        }

        return $classes;
    }


    /**
     * @param \ReflectionClass<object> $refClass
     * @param class-string<T> $name
     * @return T|null
     * @template T
     */
    public static function getAttribute(
        \ReflectionClass $refClass,
        string $name
    ): mixed {
        $refAttrs = $refClass->getAttributes(
            $name,
            \ReflectionAttribute::IS_INSTANCEOF
        );
        return (count($refAttrs) > 0) ? $refAttrs[0]->newInstance() : null;
    }


    /**
     * @template T of object
     * @param class-string<T> $filterClassName
     * @return array<string,\ReflectionClass<T>>
     */
    public function getClasses(string $filterClassName): array
    {
        $filterClass = new \ReflectionClass($filterClassName);

        /** @var array<string,\ReflectionClass<T>> */
        return $filterClass->isInterface()
            ? array_filter(
                $this->classes,
                fn (\ReflectionClass $class) => $class->isInstantiable()
                    && $class->implementsInterface($filterClass)
            )
            : array_filter(
                $this->classes,
                fn (\ReflectionClass $class) => $class->isInstantiable()
                    && $class->isSubclassOf($filterClass)
            );
    }
}
