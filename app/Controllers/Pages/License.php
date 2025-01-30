<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\Languages;

class License extends Controller
{
    public function makeData(): array
    {
        
        if (!empty($this->session['member']) && in_array('admin', Member::specialPerms($this->session['member']['username'])))
            $updated = '1/18/2025, 15:11:00 PM';
        else
            $updated = null;
            
        $page = [
            'view_name' => 'License',
            'title' => Languages::get('page', 'License'),
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