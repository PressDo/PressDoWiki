<?php
namespace Pressdo\App\Helpers\SearchEngines;

interface SearchEngineInterface
{
    public function getSearchResult(): array;
}