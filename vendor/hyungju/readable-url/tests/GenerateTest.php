<?php

namespace HyungJu\Tests;

use HyungJu\ReadableURL;
use function PHPUnit\Framework\assertEquals;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GenerateTest extends TestCase
{
    private $readableUrl;

    public function setUp(): void
    {
        $this->readableUrl = new ReadableURL();
    }

    public function testConvertToTitleCase()
    {
        $class = new ReflectionClass('HyungJu\ReadableURL');
        $method = $class->getMethod('convertToTitleCase');
        $method->setAccessible(true);

        $converted = $method->invoke(null, ['the', 'quick', 'brown', 'fox', 'jumps', 'over', 'a', 'lazy', 'dog']);
        $this->assertSame(['The', 'Quick', 'Brown', 'Fox', 'Jumps', 'Over', 'A', 'Lazy', 'Dog'], $converted);
    }

    public function testWordCountMinLimit()
    {
        try {
            new ReadableURL(true, 1, '');
        } catch (\UnexpectedValueException $exception) {
            assertEquals(0, $exception->getCode());
            assertEquals('Minimum value expected: 2', $exception->getMessage());
        }
    }

    public function testWordCountMaxLimit()
    {
        try {
            new ReadableURL(true, 11, '');
        } catch (\UnexpectedValueException $exception) {
            assertEquals(0, $exception->getCode());
            assertEquals('Maximum value expected: 10', $exception->getMessage());
        }
    }

    public function testGenerate()
    {
        $generated = $this->readableUrl->generate(); // Capitalize, 3Words, No Separator

        $capitalWordsCount = 0;
        for ($i = 0; $i < strlen($generated); $i++) {
            if (ctype_upper($generated[$i])) {
                $capitalWordsCount++;
            }
        }

        $this->assertSame(3, $capitalWordsCount);
    }

    public function testGenerateKorean()
    {
        $readableUrl = new ReadableURL(false, 3, '', 'ko');
        $generated = $readableUrl->generate(); // Capitalize, 3Words, No Separator

        $this->assertNotNull($generated);
    }

    public function testGenerateStatic()
    {
        $generated = ReadableURL::gen(); // Capitalize, 3Words, No Separator

        $capitalWordsCount = 0;
        for ($i = 0; $i < strlen($generated); $i++) {
            if (ctype_upper($generated[$i])) {
                $capitalWordsCount++;
            }
        }

        $this->assertSame(3, $capitalWordsCount);
    }
}
