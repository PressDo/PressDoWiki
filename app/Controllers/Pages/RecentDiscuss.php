<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Thread,Document,Member};
use PressDo\app\Core\{Controller,Model};
use PressDo\app\Helpers\Languages;

class RecentDiscuss extends Controller
{
    public function makeData(): array
    {
        $lo = $_GET['logtype'];
        $logtypes = [
            'normal_thread',
            'old_thread',
            'pause_thread',
            'closed_thread',
            'open_editrequest',
            'accepted_editrequest',
            'closed_editrequest',
            'old_editrequest'
        ];

        if (!in_array($lo, $logtypes))
            $lo = 'normal_thread';

        $lot = explode('_',$lo);
        $from = $lot[1];

        if ($lot[0] == 'closed')
            $status = 'close';
        elseif ($lot[0] == 'open' || $lot[0] == 'accepted' || $lot[0] == 'pause')
            $status = $lot[0];
        elseif ($lot[0] == 'old' && $lot[1] == 'editrequest')
            $status = 'open';
        else
            $status = 'normal';

        $order = ($lot[0] == 'old')? 'ASC' : 'DESC';
        $fetch = Thread::recentDiscuss($from, $status, $order);
        $resultSet = [];

        foreach ($fetch as $f){
            if (!$f['last_contributor_m']) {
                //$uuid = Model::bin2uuid($f['last_contributor_i']);
                //$ip = Member::ipLookup($uuid);
            } else {
                //$uuid = Model::bin2uuid($f['last_contributor_m']);
                //$member = Member::lookup($uuid);
            }
            $_e = Document::getTitleByUuid(Model::bin2uuid($f['document']));
            $rs = [
                'slug' => $f['urlstr'],
                'document' => ['namespace' => $_e['namespace'], 'title' => $_e['title']],
                'topic' => $f['topic'],
                'date' => $f['last_comment'],
                'logtype' => $f['logtype'],
                'ip' => $ip,
                'author' => $member,
                'user_mode' => []
            ];
            array_push($resultSet, $rs);
        }
        $page = [
            'view_name' => 'RecentDiscuss',
            'title' => Languages::get('page', 'RecentDiscuss'),
            'data' => [
                'content' => $resultSet
            ]
        ];

        return $page;
    }
}