<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/License.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        
        if (!empty($this->session->member) && in_array('admin', Models::special_perms($this->session->member->username)))
            $updated = '7/10/2021, 15:11:00 PM';
        else
            $updated = null;
            
        $page = [
            'view_name' => '',
            'title' => Lang::get('page')['License'],
            'data' => [
                'version' => '2107a',
                'updated' => $updated,
                'hash' => 'ffede52'
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}