<?php
namespace PressDo\App\Controllers\Pages\Member;

use PressDo\App\Models\{Document,Star};
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\Languages;

class StarredDocuments extends Controller
{
    public function makeData()
    {
        if (!$this->session['member']) {
            Header('Location: /member/login?redirect='.$_SERVER['REQUEST_URI']);
            exit;
        }

        $starred = Star::getStarred($this->session['uuid']);
        
        if (count($starred) > 0) {
            $starred_list = Star::getStarredModifiedDate($starred);
        } else
            $starred_list = null;

        $error = null;
        $page = [
            'view_name' => '',
            'title' => Languages::get('page', 'starred_documents'),
            'data' => [
                'error' => $error,
                'starred_documents' => $starred_list
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}