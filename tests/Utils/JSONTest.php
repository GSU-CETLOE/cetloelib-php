<?php

declare(strict_types=1);

namespace Cetloe\Tests\Utils;

use Cetloe\Utils\JSON;
use PHPUnit\Framework\TestCase;

final class JSONTest extends TestCase
{
    public function testEncodePrettyPrintsByDefault(): void
    {
        self::assertSame("{\n    \"a\": 1\n}", JSON::encode(['a' => 1]));
    }


    public function testEncodeHonorsFlags(): void
    {
        self::assertSame('{"a":"\/"}', JSON::encode(['a' => '/'], 512, 0));
        self::assertSame('{"a":"/"}', JSON::encode(['a' => '/'], 512, JSON_UNESCAPED_SLASHES));
    }


    public function testEncodeThrowsOnUnencodableValue(): void
    {
        $this->expectException(\JsonException::class);
        JSON::encode("\xB1\x31");
    }


    public function testDecodeArrayReturnsAssociativeArray(): void
    {
        self::assertSame(['a' => ['b' => 2]], JSON::decodeArray('{"a":{"b":2}}'));
        self::assertSame([1, 2], JSON::decodeArray('[1,2]'));
    }


    public function testDecodeArrayRejectsScalarJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Decoded JSON is not an array');
        JSON::decodeArray('5');
    }


    public function testDecodeArrayThrowsOnMalformedJson(): void
    {
        $this->expectException(\JsonException::class);
        JSON::decodeArray('{not json');
    }


    public function testDecodeObjectReturnsStdClass(): void
    {
        $value = JSON::decodeObject('{"a":1}');
        self::assertSame(1, $value->a);
    }


    public function testDecodeObjectRejectsNonObjectJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Decoded JSON is not an object');
        JSON::decodeObject('[1]');
    }
}
