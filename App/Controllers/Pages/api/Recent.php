<?php
namespace PressDo\App\Controllers\Pages\api;

use PressDo\App\Models\{History,Document};
use PressDo\App\Core\Controller;

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