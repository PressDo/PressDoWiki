<?php

use HyungJu\lang\ko\Ko;
use PHPUnit\Framework\TestCase;

class KoTest extends TestCase
{
    public function testKoreanVowel()
    {
        $ko = new Ko();
        $this->assertTrue($ko->isVowel('안녕하세요'));
    }
}
