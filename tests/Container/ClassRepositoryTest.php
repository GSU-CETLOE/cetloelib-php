<?php

declare(strict_types=1);

namespace Cetloe\Tests\Container;

use Cetloe\Container\ClassRepository;
use Cetloe\Tests\Fixtures\App\Command\HelloCommand;
use Cetloe\Tests\Fixtures\App\Shape\AbstractShape;
use Cetloe\Tests\Fixtures\App\Shape\Circle;
use Cetloe\Tests\Fixtures\App\Shape\Color;
use Cetloe\Tests\Fixtures\App\Shape\ShapeInterface;
use Cetloe\Tests\Fixtures\App\Shape\ShapeTrait;
use Cetloe\Tests\Fixtures\App\Shape\Square;
use Cetloe\Tests\Support\FixtureApp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Attribute\AsCommand;

final class ClassRepositoryTest extends TestCase
{
    private function createRepository(): ClassRepository
    {
        return new ClassRepository(FixtureApp::dir(), FixtureApp::NAMESPACE);
    }


    public function testEveryPhpFileUnderSrcIsIndexedByClassName(): void
    {
        $classes = $this->createRepository()->classes;

        self::assertArrayHasKey(Circle::class, $classes);
        self::assertArrayHasKey(ShapeInterface::class, $classes);
        self::assertArrayHasKey(ShapeTrait::class, $classes);
        self::assertArrayHasKey(Color::class, $classes);
        self::assertArrayHasKey(AbstractShape::class, $classes);
        self::assertSame(Circle::class, $classes[Circle::class]->getName());
    }


    public function testMissingSrcDirectoryIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not a directory');
        new ClassRepository(FixtureApp::dir() . '/src', FixtureApp::NAMESPACE);
    }


    public function testEmptyNamespaceIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing application namespace');
        new ClassRepository(FixtureApp::dir(), '');
    }


    public function testFileWhoseClassCannotBeLoadedIsReported(): void
    {
        $this->expectException(\ReflectionException::class);
        $this->expectExceptionMessage('Class not found: Cetloe\Tests\Fixtures\BrokenApp\Nope');
        new ClassRepository(dirname(FixtureApp::dir()) . '/BrokenApp', 'Cetloe\Tests\Fixtures\BrokenApp');
    }


    public function testGetClassesByInterfaceReturnsOnlyInstantiableImplementations(): void
    {
        $shapes = $this->createRepository()->getClasses(ShapeInterface::class);

        self::assertSame([Circle::class, Square::class], array_keys($shapes));
    }


    public function testGetClassesByParentReturnsOnlyInstantiableSubclasses(): void
    {
        $shapes = $this->createRepository()->getClasses(AbstractShape::class);

        self::assertSame([Circle::class], array_keys($shapes));
    }


    public function testGetReflectionClassesFiltersAnArbitraryListByInterface(): void
    {
        $classes = ClassRepository::getReflectionClasses(
            [Circle::class, Square::class, AbstractShape::class, 'Not\A\Class', \stdClass::class],
            ShapeInterface::class
        );

        self::assertSame([Circle::class, Square::class], array_keys($classes));
    }


    public function testGetReflectionClassesFiltersAnArbitraryListByParent(): void
    {
        $classes = ClassRepository::getReflectionClasses(
            [Circle::class, Square::class, 'Not\A\Class'],
            AbstractShape::class
        );

        self::assertSame([Circle::class], array_keys($classes));
    }


    public function testGetAttributeInstantiatesTheAttributeWhenPresent(): void
    {
        $attribute = ClassRepository::getAttribute(new \ReflectionClass(HelloCommand::class), AsCommand::class);

        self::assertInstanceOf(AsCommand::class, $attribute);
        self::assertSame(HelloCommand::NAME, $attribute->name);
    }


    public function testGetAttributeReturnsNullWhenAbsent(): void
    {
        self::assertNull(ClassRepository::getAttribute(new \ReflectionClass(Circle::class), AsCommand::class));
    }
}
