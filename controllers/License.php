<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/blank.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        
        if (!empty($this->session->member) && in_array('admin', Models::special_perms($this->session->member->username)))
            $updated = '1/18/2025, 15:11:00 PM';
        else
            $updated = null;
            
        $page = [
            'view_name' => 'License',
            'title' => Lang::get('page')['License'],
            'data' => [
                'version' => '2501c',
                'updated' => $updated,
                'hash' => '38aacf4'
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}