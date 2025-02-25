<?php
namespace Pressdo\app\Helpers\SearchEngines;

use PressDo\app\Models\Search;

class SQL
{
    public string $keyword, $target;
    public string|null $namespace;

    public function getSearchResult(): array
    {
        return Search::hardSearch($this->keyword, $this->target, $this->namespace);
    }
}