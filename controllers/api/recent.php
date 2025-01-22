<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/RecentChanges.php';

class WikiPage extends WikiCore
{
    public function make_data()
    {
        $fetch = Models::RecentChanges(sidebar: true);
        $resultSet = [];
        
        foreach($fetch as $f){
            $_e = Models::get_doc_title($f['uuid']);
            $rs = array(
                'document' => ['namespace' => $_e['namespace'], 'title' => $_e['title']],
                'status' => $f['status'],
                'date' => $f['datetime']
            );
            array_push($resultSet, $rs);
        }

        Header('Content-type: application/json');
        echo json_encode($resultSet, JSON_UNESCAPED_UNICODE);
        exit;
    }
}