<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/RandomPage.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        $page = [
            'view_name' => 'RandomPage',
            'title' => Lang::get('page')['RandomPage'],
            'data' => [
                'content' => []
            ],
            'menus' => [],
            'customData' => []
        ];

        $page['data']['content'] = $resultSet;

        return $page;
    }
}