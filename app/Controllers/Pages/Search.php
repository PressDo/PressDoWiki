<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Search as S;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,Namespaces};

class Search extends Controller
{
    private const SEARCH_TARGETS = [
        'title_content',
        'title',
        'content',
        'raw'
    ];

    public function makeData(): array
    {
        if (!empty($_GET['q'])) {
            $namespace = null;
            if (in_array($_GET['namespace'], Namespaces::all()))
                $namespace = $_GET['namespace']; // 추후 검색 제외 네임스페이스 구현

            $target = 'title_content';
            if (in_array($_GET['target'], self::SEARCH_TARGETS))
                $target = $_GET['target'];

            $searchData = self::searchQuery($_GET['q'], $target, $namespace);

            foreach ($searchData as $k => $s) {
                $searchData[$k] = [
                    'document' => [
                        'namespace' => $s['namespace'],
                        'title' => $s['title'],
                        'forceShowNamespace' => self::forceShowNamespace($s['namespace'], $s['title'])
                    ],
                    'content' => mb_substr($s['text'], 0, 256)
                ];
            }
        } else
            $searchData = [];

        $page = [
            'view_name' => 'search',
            'title' => Languages::get('page', 'Search'),
            'data' => [
                'body' => [
                    'query' => $_GET['q'],
                    'target' => 'title_content',
                    'namespace' => $namespace
                ],
                'namespaces' => Namespaces::all(),
                'page' => 0,
                'result' => $searchData,
                'resultCount' => 0,
                'period' => 0
            ]
        ];
        return $page;
    }

    private function searchQuery(string $keyword, string $target, ?string $namespace): array
    {
        // if (DefaultConfig::get('wiki.search_engine') == 'SQL')
        return S::hardSearch($keyword, $target, $namespace);
    }
}