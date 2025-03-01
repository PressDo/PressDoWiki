<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Namespaces,DefaultConfig};

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
                    // 추후 검색어의 정확한 파싱 + 텍스트 발췌 개선 필요
                    'content' => str_replace($_GET['q'], '<span class="search-highlight">'.$_GET['q'].'</span>', mb_substr($s['text'], 0, 256))
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

    /**
     * Search with designated search engine
     * @param string $keyword
     * @param string $target
     * @param mixed $namespace
     * @return array
     */
    private function searchQuery(string $keyword, string $target, ?string $namespace): array
    {
        $engine = (new \ReflectionClass('PressDo\App\Helpers\SearchEngines\\'.DefaultConfig::get('searchengine.type')))->newInstance();
        $engine->keyword = $keyword;
        $engine->target = $target;
        $engine->namespace = $namespace;
        return $engine->getSearchResult();
    }
}