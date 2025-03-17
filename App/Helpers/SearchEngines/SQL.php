<?php
namespace Pressdo\App\Helpers\SearchEngines;

use PressDo\App\Models\Search;

class SQL implements SearchEngineInterface
{
    public string $keyword, $target;
    public string|null $namespace;

    public function getSearchResult(): array
    {
        return Search::hardSearch($this->keyword, $this->target, $this->namespace);
    }
}