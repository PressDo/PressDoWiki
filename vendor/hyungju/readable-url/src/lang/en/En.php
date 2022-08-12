<?php

namespace HyungJu\lang\en;

use HyungJu\lang\Language;

class En extends Language
{
    public function getLangCode()
    {
        return 'en';
    }

    public function getGluesForVowel()
    {
        return ['an', 'the'];
    }

    public function getGluesForNonVowel()
    {
        return ['a'];
    }

    public function getVowels()
    {
        return ['a', 'e', 'i', 'o', 'u'];
    }

    public function isVowel(string $word)
    {
        $isVowel = false;

        for ($i = 0; $i < 5; $i++) {
            if ($this->getVowels()[$i] === $word[0]) {
                $isVowel = true;
                break;
            }
        }

        return $isVowel;
    }
}
