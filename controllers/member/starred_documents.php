<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/starred_documents.php';

use PressDo\Models;

class WikiPage extends WikiCore
{
    public function make_data()
    {
        if(!$this->session->member){
            Header('Location: /member/login?redirect='.$this->server->REQUEST_URI);
            exit;
        }

        $starred = Models::get_starred($this->session->member->username);
        
        if(count($starred) > 0){
            $starred_mod = Models::get_modified_date($starred);
            $starred_name = Models::get_bulk_doc_title($starred);
            $starred_doc = [];

            $cnt = count($starred);
            for($i=0; $i<$cnt; $i++){
                $starred_doc[$starred_mod[$i]['docid']]['datetime'] = $starred_mod[$i]['datetime'];
            }
            for($i=0; $i<$cnt; $i++){
                $starred_doc[$starred_name[$i]['docid']]['title'] = $this->make_title($starred_name[$i]['namespace'], $starred_name[$i]['title']);
            }
        }else
            $starred_doc = null;

        $error = null;
        $page = [
            'view_name' => '',
            'title' => Lang::get('page')['starred_documents'],
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