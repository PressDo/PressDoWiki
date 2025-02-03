<?php
namespace PressDo\app\Controllers\Pages\api;

use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Namespaces,Database};

class Preview extends Controller
{
    public function makeData(): never
    {
        $content = self::readSyntax($_POST['text'], [
            'title' => $_POST['title'],
            'db' => Database::getInstance(),
            'namespace' => Namespaces::all(),
            'thread' => false
        ]);

        echo $content['html'];
        exit;
    }
}