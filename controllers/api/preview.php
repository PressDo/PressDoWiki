<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/blank.php';

class WikiPage extends WikiCore
{
    public function make_data()
    {
        $content = $this::readSyntax($_POST['text'], Config::get('mark'), [
            'title' => $_POST['title'],
            'noredirect' => '1',
            'db' => DB::getInstance(),
            'namespace' => Namespaces::all(),
            'thread' => false
        ]);

        echo $content['html'];
        exit;
    }
}