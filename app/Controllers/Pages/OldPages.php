<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Document;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\Languages;

class OldPages extends Controller
{
    public function makeData(): array
    {
        $from = 1;
        $count = 100;
        
        if ($_GET['from'] > 0)
            $from = $_GET['from'];
        elseif ($_GET['until'] > 0){
            if ($_GET['until'] < 100) 
                $count = $_GET['until'];
            else
                $from = $_GET['until'] - 100;
        }

        $resultSet = Document::getOldestPages($from, $count);

        if (count($resultSet) === $count + 1) {
            $next_v = $from + $count;
            array_pop($resultSet);
        }

        $page = [
            'view_name' => 'OldPages',
            'title' => Languages::get('page', 'OldPages'),
            'data' => [
                'content' => [],
                'prev_ver' => $from > 1 ? $from - 1 : null,
                'next_ver' => $next_v ?? null
            ],
            'menus' => [],
            'customData' => []
        ];
        foreach ($resultSet as $k => $r) {
            $resultSet[$k]['forceShowNamespace'] = self::forceShowNamespace($r['namespace'], $r['title']);
        }

        $page['data']['content'] = $resultSet;

        return $page;
    }
}