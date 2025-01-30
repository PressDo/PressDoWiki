<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Document;
use PressDo\app\Core\Controller;

class Random extends Controller
{
    public function makeData(): void
    {
        $r = Document::getRandom()[0];
        Header('Location: /w/'.self::makeTitle($r['namespace'], $r['title']));
    }
}