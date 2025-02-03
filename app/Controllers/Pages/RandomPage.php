<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Document;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,Namespaces};

class RandomPage extends Controller
{
    public function makeData(): array
    {
        $namespace = in_array($_GET['namespace'], Namespaces::all()) ? $_GET['namespace'] : '문서';
        $page = [
            'view_name' => 'RandomPage',
            'title' => Languages::get('page', 'RandomPage'),
            'data' => [
                'namespace' => $namespace,
                'content' => []
            ],
            'menus' => [],
            'customData' => []
        ];
        $resultSet = Document::getRandom($namespace, 20);

        $page['data']['content'] = $resultSet;

        return $page;
    }
}