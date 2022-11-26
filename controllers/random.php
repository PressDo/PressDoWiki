<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/random.php';

use PressDo\Models;

class WikiPage extends WikiCore
{
    public function make_data()
    {
        $r = Models::get_random();
        Header('Location: /w/'.self::make_title($r['namespace'], $r['title']));
    }
}