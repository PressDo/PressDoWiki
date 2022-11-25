<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/history.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        list($rawns, $namespace, $title) = self::parse_title($this->uri_data->title);

        $ACL = new WikiACL($rawns, $title, 'read', $this->session, $this->error);
        $ACL->check();
        $page = [
            'view_name' => 'history',
            'title' => $this->uri_data->title.' ('.Lang::get('page')['history'].')',
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'history' => []
            ],
            'menus' => [],
            'customData' => []
        ];
        
        if ($this->error->code == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;//$this::make_error();
        }

        if(Models::exist($rawns,$title)){
            if(isset($_GET['from'])) $from = $_GET['from'];
            if(isset($_GET['until'])) $until = $_GET['until'];
            $fetch = Models::loadHistory($rawns,$title, $from, $until);
            $ver = Models::get_version($rawns,$title);
            $l = Models::get_rev_time($rawns,$title, 1);
            $localConfig = [];
            $cn = count($fetch);
            $cl = ($cn < 31)? $cn:$cn-1;

            foreach($fetch as $f){
                array_push($page['data']['history'], [
                    'rev' => $f['rev'],
                    'log' => $f['comment'],
                    'date' => $f['datetime'],
                    'count' => $f['count'],
                    'logtype' => $f['action'],
                    'target_rev' => $f['reverted_version'],
                    'author' => $f['contributor_m'],
                    'ip' => $f['contributor_i'],
                    'style' => null,
                    'blocked' => null,
                    'edit_request' => $f['edit_request_uri'],
                    'acl' => $f['acl_changed'],
                    'from' => $f['moved_from'],
                    'to' => $f['moved_to'],
                    'user_mode' => []
                ]);
            }
            $page['data']['prev_ver'] = ($fetch[0]['rev'] == $ver)? null : $fetch[0]['rev'] + 1;
            $page['data']['next_ver'] = (end($fetch)['rev'] == 1)? null : end($fetch)['rev'] - 1;
            $page['data']['initial_date'] = $l;
        }else{
            $this->error = (object) ['code' => 'no_such_document'];
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
        }
        return $page;
    }
}