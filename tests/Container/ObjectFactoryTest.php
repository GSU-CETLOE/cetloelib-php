<?php

declare(strict_types=1);

namespace Cetloe\Tests\Container;

use Cetloe\Container\ObjectFactory;
use Cetloe\Tests\Fixtures\Container\Point;
use PHPUnit\Framework\TestCase;

final class ObjectFactoryTest extends TestCase
{
    public function testExistingInstanceIsReturnedUntouched(): void
    {
        $point = new Point(1, 2);
        $created = ObjectFactory::createObject($point, Point::class, fn (array $v): Point => new Point(0, 0));

        self::assertSame($point, $created);
    }


    public function testArrayLikeValuesAreConvertedAndPassedToTheConstructor(): void
    {
        $construct = fn (array $v): Point => new Point((int)$v['x'], (int)$v['y']);

        $fromArray = ObjectFactory::createObject(['x' => 1, 'y' => 2], Point::class, $construct);
        $fromJson = ObjectFactory::createObject('{"x":3,"y":4}', Point::class, $construct);
        $fromObject = ObjectFactory::createObject((object)['x' => 5, 'y' => 6], Point::class, $construct);

        self::assertEquals(new Point(1, 2), $fromArray);
        self::assertEquals(new Point(3, 4), $fromJson);
        self::assertEquals(new Point(5, 6), $fromObject);
    }


    public function testFailuresAreWrappedWithTheClassName(): void
    {
        try {
            ObjectFactory::createObject(42, Point::class, fn (array $v): Point => new Point(0, 0));
            self::fail('Expected a RuntimeException');
        } catch (\RuntimeException $e) {
            self::assertSame('Error creating instance: ' . Point::class, $e->getMessage());
            self::assertInstanceOf(\ValueError::class, $e->getPrevious());
        }
    }
}
