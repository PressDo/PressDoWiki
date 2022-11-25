<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/Upload.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        
        $dataset = Models::get_upload_info();
            
        $page = [
            'view_name' => 'Upload',
            'title' => Lang::get('page')['Upload'],
            'data' => [
                'Licenses' => $dataset['License'],
                'Categories' => $dataset['Category']
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}