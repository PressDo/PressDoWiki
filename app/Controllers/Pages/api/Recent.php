<?php
namespace PressDo\app\Controllers\Pages\api;

use PressDo\app\Models\{History,Document};
use PressDo\app\Core\Controller;

class Recent extends Controller
{
    public function makeData(): never
    {
        $fetch = History::getRecentSidebar();
        $resultSet = [];
        
        foreach($fetch as $f){
            $_e = Document::getTitleByUuid(Document::bin2uuid($f['document']));
            $rs = [
                'document' => [
                    'namespace' => $_e['namespace'],
                    'title' => $_e['title'],
                    'forceShowNamespace' => self::forceShowNamespace($_e['namespace'], $_e['title'])
                ],
                'status' => $_e['status'],
                'date' => $f['datetime']
            ];
            array_push($resultSet, $rs);
        }

        Header('Content-type: application/json; charset=utf-8');
        echo json_encode($resultSet, JSON_UNESCAPED_UNICODE);
        exit;
    }
}