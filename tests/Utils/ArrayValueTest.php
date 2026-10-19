<?php

declare(strict_types=1);

namespace Cetloe\Tests\Utils;

use Cetloe\Tests\Support\TempDir;
use Cetloe\Utils\ArrayValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ArrayValueTest extends TestCase
{
    public function testConvertToArrayAcceptsArraysObjectsAndJson(): void
    {
        self::assertSame(['a' => 1], ArrayValue::convertToArray(['a' => 1]));
        self::assertSame(['a' => 1], ArrayValue::convertToArray((object)['a' => 1]));
        self::assertSame(['a' => 1], ArrayValue::convertToArray('{"a":1}'));
    }


    public function testConvertToArrayReadsJsonFromFilePath(): void
    {
        $dir = TempDir::create();
        try {
            $path = $dir . '/values.json';
            file_put_contents($path, '{"from":"file"}');
            self::assertSame(['from' => 'file'], ArrayValue::convertToArray($path));
        } finally {
            TempDir::remove($dir);
        }
    }


    public function testConvertToArrayRejectsUnsupportedValue(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Unable to convert to array');
        ArrayValue::convertToArray(42);
    }


    public function testConvertToStringArrayStringifiesScalarsAndDropsTheRest(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'str';
            }
        };

        self::assertSame(
            ['a' => '1', 'b' => '2.5', 'c' => '1', 'd' => 'x', 'e' => 'str'],
            ArrayValue::convertToStringArray([
                'a' => 1,
                'b' => 2.5,
                'c' => true,
                'd' => 'x',
                'e' => $stringable,
                'f' => null,
                'g' => [1],
                'h' => new \stdClass(),
            ])
        );
    }


    public function testConvertToStringArrayRejectsUnsupportedValue(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Unable to convert to string array');
        ArrayValue::convertToStringArray(3.14);
    }


    /**
     * @return array<string,array{0:mixed,1:bool}>
     */
    public static function boolValues(): array
    {
        return [
            'native true' => [true, true],
            'native false' => [false, false],
            'yes' => ['yes', true],
            'Y' => ['Y', true],
            'on' => ['on', true],
            'T' => ['t', true],
            'TRUE' => ['TRUE', true],
            'X' => ['x', true],
            'one' => ['1', true],
            'int one' => [1, true],
            'no' => ['no', false],
            'zero' => ['0', false],
            'empty' => ['', false],
            'other' => ['maybe', false],
        ];
    }


    /**
     * @param mixed $raw
     * @param bool $expected
     */
    #[DataProvider('boolValues')]
    public function testGetBoolCastsRecognizedValues(mixed $raw, bool $expected): void
    {
        self::assertSame($expected, ArrayValue::getBool(['k' => $raw], 'k'));
        self::assertSame($expected, ArrayValue::getBoolNull(['k' => $raw], 'k'));
    }


    public function testGetBoolRejectsMissingOrNonScalarValues(): void
    {
        self::assertNull(ArrayValue::getBoolNull([], 'k'));

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Unable to get value: k');
        ArrayValue::getBool(['k' => []], 'k');
    }


    public function testGetBoolRejectsMissingKey(): void
    {
        $this->expectException(\ValueError::class);
        ArrayValue::getBool([], 'k');
    }


    public function testGetFloatCastsScalars(): void
    {
        self::assertSame(1.5, ArrayValue::getFloat(['k' => 1.5], 'k'));
        self::assertSame(2.0, ArrayValue::getFloat(['k' => '2'], 'k'));
        self::assertSame(3.0, ArrayValue::getFloat(['k' => 3], 'k'));
        self::assertNull(ArrayValue::getFloatNull(['k' => null], 'k'));

        $this->expectException(\ValueError::class);
        ArrayValue::getFloat(['k' => null], 'k');
    }


    public function testGetIntCastsScalars(): void
    {
        self::assertSame(7, ArrayValue::getInt(['k' => 7], 'k'));
        self::assertSame(8, ArrayValue::getInt(['k' => '8'], 'k'));
        self::assertSame(9, ArrayValue::getInt(['k' => 9.9], 'k'));
        self::assertNull(ArrayValue::getIntNull([], 'k'));

        $this->expectException(\ValueError::class);
        ArrayValue::getIntNull(['k' => []], 'k');
    }


    public function testGetStringCastsScalars(): void
    {
        self::assertSame('s', ArrayValue::getString(['k' => 's'], 'k'));
        self::assertSame('5', ArrayValue::getString(['k' => 5], 'k'));
        self::assertSame('1', ArrayValue::getString(['k' => true], 'k'));
        self::assertNull(ArrayValue::getStringNull(['k' => null], 'k'));

        $this->expectException(\ValueError::class);
        ArrayValue::getString(['k' => new \stdClass()], 'k');
    }


    public function testGetArrayDecodesJsonAndObjects(): void
    {
        self::assertSame([1], ArrayValue::getArray(['k' => [1]], 'k'));
        self::assertSame(['a' => 1], ArrayValue::getArray(['k' => '{"a":1}'], 'k'));
        self::assertSame(['a' => 1], ArrayValue::getArray(['k' => (object)['a' => 1]], 'k'));
        self::assertNull(ArrayValue::getArrayNull([], 'k'));

        $this->expectException(\ValueError::class);
        ArrayValue::getArray(['k' => 5], 'k');
    }


    public function testGetStringArrayAlwaysNormalizes(): void
    {
        self::assertSame(['a' => '1'], ArrayValue::getStringArray(['k' => ['a' => 1]], 'k'));
        self::assertSame(['a' => '1'], ArrayValue::getStringArray(['k' => '{"a":1}'], 'k'));
        self::assertNull(ArrayValue::getStringArrayNull(['k' => null], 'k'));

        $this->expectException(\ValueError::class);
        ArrayValue::getStringArray(['k' => 5], 'k');
    }


    public function testGetObjectConvertsArraysAndJson(): void
    {
        $object = new \stdClass();
        self::assertSame($object, ArrayValue::getObject(['k' => $object], 'k'));
        self::assertEquals((object)['a' => 1], ArrayValue::getObject(['k' => ['a' => 1]], 'k'));
        self::assertEquals((object)['a' => 1], ArrayValue::getObject(['k' => '{"a":1}'], 'k'));
        self::assertNull(ArrayValue::getObjectNull([], 'k'));

        $this->expectException(\ValueError::class);
        ArrayValue::getObject(['k' => 5], 'k');
    }


    public function testGetDateTimeAcceptsObjectsStringsAndTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        self::assertSame($now, ArrayValue::getDateTime(['k' => $now], 'k'));
        self::assertSame(
            '2024-01-02T03:04:05+00:00',
            ArrayValue::getDateTime(['k' => '2024-01-02T03:04:05Z'], 'k')->format(DATE_ATOM)
        );
        self::assertSame(86400, ArrayValue::getDateTime(['k' => 86400], 'k')->getTimestamp());
        self::assertNull(ArrayValue::getDateTimeNull([], 'k'));

        $this->expectException(\ValueError::class);
        ArrayValue::getDateTime(['k' => 1.5], 'k');
    }


    public function testGetResourceOnlyAcceptsResources(): void
    {
        $stream = fopen('php://memory', 'r');
        self::assertSame($stream, ArrayValue::getResource(['k' => $stream], 'k'));
        self::assertSame($stream, ArrayValue::getResourceNull(['k' => $stream], 'k'));
        self::assertNull(ArrayValue::getResourceNull([], 'k'));
        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->expectException(\ValueError::class);
        ArrayValue::getResource(['k' => 'not a resource'], 'k');
    }


    public function testMultipleNamesFallBackToTheFirstUsable(): void
    {
        $values = ['second' => '2', 'third' => 'x'];

        self::assertSame(2, ArrayValue::getInt($values, ['first', 'second', 'third']));
        self::assertSame('x', ArrayValue::getString($values, ['first', 'third']));
    }


    public function testMultipleNamesReportAllNamesWhenNoneUsable(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Unable to get value: first,second');
        ArrayValue::getArray(['first' => 1], ['first', 'second']);
    }
}
