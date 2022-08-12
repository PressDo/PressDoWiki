<?php

namespace HyungJu\lang\ko;

use HyungJu\lang\Language;

class Ko extends Language
{
    public function getLangCode()
    {
        return 'ko';
    }

    public function getGluesForVowel()
    {
        return ['그'];
    }

    public function getGluesForNonVowel()
    {
        return ['그'];
    }

    public function getVowels()
    {
        return [];
    }

    public function isVowel(string $word)
    {
        return true;
    }
}
