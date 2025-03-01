<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\Document;
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Namespaces};

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
        foreach ($resultSet as $k => $r) {
            $resultSet[$k]['forceShowNamespace'] = self::forceShowNamespace($r['namespace'], $r['title']);
        }

        $page['data']['content'] = $resultSet;

        return $page;
    }
}