<?php

namespace HyungJu\lang;

abstract class Language
{
    abstract public function isVowel(string $word);

    abstract public function getLangCode();

    abstract public function getGluesForVowel();

    abstract public function getGluesForNonVowel();

    abstract public function getVowels();

    public function pickOneAdjective(): string
    {
        $adjectives = explode(' ', file_get_contents(__DIR__.'/'.$this->getLangCode().'/words/adjectives.txt'));

        return $adjectives[rand(0, count($adjectives) - 1)];
    }

    public function pickOneNoun(): string
    {
        $nouns = explode(' ', file_get_contents(__DIR__.'/'.$this->getLangCode().'/words/nouns.txt'));

        return $nouns[rand(0, count($nouns) - 1)];
    }

    public function pickOneGlueForVowel(): string
    {
        $gluesForVowel = $this->getGluesForVowel();

        return $gluesForVowel[rand(0, count($gluesForVowel) - 1)];
    }

    public function pickOneGlueForNonVowel(): string
    {
        $gluesForNonVowel = $this->getGluesForNonVowel();

        return $gluesForNonVowel[rand(0, count($gluesForNonVowel) - 1)];
    }

    public function pickOneGlueFor(string $word): string
    {
        if ($this->isVowel($word)) {
            return $this->pickOneGlueForVowel();
        } else {
            return $this->pickOneGlueForNonVowel();
        }
    }

    public function pickMultipleAdjectives(int $numbers): array
    {
        $res = [];
        for ($i = 0; $i < $numbers; $i++) {
            $res[] = $this->pickOneAdjective();
        }

        return $res;
    }

    public function pickMultipleNouns(int $numbers): array
    {
        $res = [];
        for ($i = 0; $i < $numbers; $i++) {
            $res[] = $this->pickOneAdjective();
        }

        return $res;
    }
}
