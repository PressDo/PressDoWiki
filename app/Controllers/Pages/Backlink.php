<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,Backlink as Models};
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Namespaces,Languages,Config};

class Backlink extends Controller
{
    /*public const BACKLINK_ALL = 0;
    public const BACKLINK_LINK = 1;
    public const BACKLINK_FILE = 2;
    public const BACKLINK_INCLUDE = 4;
    public const BACKLINK_REDIRECT = 8;*/

    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace,$title);

        $page = [
            'view_name' => 'backlink',
            'title' => $this->uri_data->title,
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                ],
                'backlink_count' => [],
                'backlink' => []
            ],
            'menus' => [],
            'customData' => []
        ];

        if (isset($_GET['flag']) && in_array(intval($_GET['flag']), [0, 1, 2, 4, 8])) {
            $flag = [
                0 => null,
                1 => 'link',
                2 => 'file',
                4 => 'include',
                8 => 'redirect'
            ];
            $type = $flag[intval($_GET['flag'])];
        } else
            $type = null;

        $bl_count = Models::count($namespace, $title);
        
        foreach ($bl_count as $b)
            array_push($page['data']['backlink_count'], ['namespace' => $b['namespace'], 'count' => $b['cnt']]);
        

        if (isset($_GET['namespace']) && in_array($_GET['namespace'], Namespaces::all()))
            $target_ns = $_GET['namespace'];
        elseif (count($bl_count) > 0)
            $target_ns = $bl_count[0]['namespace'];
        
        // skip this if there's no backlink
        if (isset($target_ns)) {
            $backlinks = Models::get($namespace, $title, $target_ns, $type);
            ksort($backlinks);
            foreach ($backlinks as $title => $b) {
                // backlink 정렬
                $firstchar = iconv_substr($title, 0, 1);
                $head = self::is_hangeul($firstchar) ? self::ko_head($firstchar) : $firstchar;

                if(!isset($page['data']['backlink'][$head]))
                    $page['data']['backlink'][$head] = [];
                
                array_push($page['data']['backlink'][$head], [
                    'document' => [
                        'namespace' => $b[0]['namespace'], 
                        'title' => $title, 
                        'forceShowNamespace' => self::forceShowNamespace($b[0]['namespace'], $title)
                    ],
                    'type' => $b[0]['type']
                ]);
            }
        }
        return $page;
    }
}