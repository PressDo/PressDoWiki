<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\Document;
use PressDo\App\Core\Controller;

class Random extends Controller
{
    public function makeData(): void
    {
        $r = Document::getRandom()[0];
        Header('Location: /w/'.self::makeTitle($r['namespace'], $r['title']));
        exit;
    }
}