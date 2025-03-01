<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\Document;
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Namespaces};

class UncategorizedPages extends Controller
{
    public function makeData(): array
    {
        $from = 1;
        $count = 100;
        $namespace = in_array($_GET['namespace'], Namespaces::all()) ? $_GET['namespace'] : '문서';
        
        if ($_GET['from'] > 0)
            $from = $_GET['from'];
        elseif ($_GET['until'] > 0){
            if ($_GET['until'] < 100) 
                $count = $_GET['until'];
            else
                $from = $_GET['until'] - 100;
        }

        $namespace = in_array($_GET['namespace'], Namespaces::all()) ? $_GET['namespace'] : '문서';
        $resultSet = Document::getUncategorizedPages($namespace, $from, $count);

        if (count($resultSet) === $count + 1) {
            $next_v = $from + $count;
            array_pop($resultSet);
        }

        $page = [
            'view_name' => 'UncategorizedPages',
            'title' => Languages::get('page', 'UncategorizedPages'),
            'data' => [
                'namespace' => $namespace,
                'content' => [],
                'prev_ver' => $from > 1 ? $from - 1 : null,
                'next_ver' => $next_v ?? null
            ],
            'menus' => [],
            'customData' => []
        ];

        $page['data']['content'] = $resultSet;

        return $page;
    }
}