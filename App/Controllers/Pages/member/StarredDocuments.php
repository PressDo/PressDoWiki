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

        $starred = Star::getStarred($this->session['member']['username']);
        
        if (count($starred) > 0) {
            $starred_mod = Star::getStarredModifiedDate($starred);
            $starred_name = Document::getBulkTitle($starred);
            $starred_doc = [];

            $cnt = count($starred);
            for ($i=0; $i<$cnt; $i++) {
                $starred_doc[$starred_mod[$i]['uuid']]['datetime'] = $starred_mod[$i]['datetime'];
            }
            for ($i=0; $i<$cnt; $i++) {
                $starred_doc[$starred_name[$i]['uuid']]['title'] = self::makeTitle($starred_name[$i]['namespace'], $starred_name[$i]['title']);
            }
        } else
            $starred_doc = null;

        $error = null;
        $page = [
            'view_name' => '',
            'title' => Languages::get('page', 'starred_documents'),
            'data' => [
                'error' => $error,
                'starred_documents' => $starred_doc
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}