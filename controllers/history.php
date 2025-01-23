<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/history.php';
require 'controllers/lib/libacl.php';

use PressDo\{Models,WikiACL};
class WikiPage extends WikiCore
{
    public function make_data()
    {
        [$namespace, $title] = self::parse_title($this->uri_data->title);
        $uuid = Models::get_doc_uuid($namespace, $title, $backlinkrefreshed);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $this->error);
        $ACL->check('read');

        $page = [
            'view_name' => 'history',
            'title' => $this->uri_data->title,
            'subtitle' => Lang::get('page')['history'],
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

        if($uuid !== false){
            if(isset($_GET['from'])) $from = $_GET['from'];
            if(isset($_GET['until'])) $until = $_GET['until'];

            $fetch = Models::loadHistory($uuid, $from, $until);
            $ver = Models::get_version($uuid);
            $l = Models::get_rev_time($namespace,$title, 1);
            $localConfig = [];
            $cn = count($fetch);
            $cl = ($cn < 31)? $cn:$cn-1;

            foreach($fetch as $f){
                if($f['contributor_i'] !== null){
                    $uuid = Models::bin2uuid($f['contributor_i']);
                    $ip = Models::ip_lookup($uuid);
                }elseif($f['contributor_m'] !== null){
                    $uuid = Models::bin2uuid($f['contributor_m']);
                    $member = Models::member_lookup($uuid);
                }

                array_push($page['data']['history'], [
                    'rev' => $f['rev'],
                    'uuid' => Models::bin2uuid($f['uuid']),
                    'log' => $f['comment'],
                    'date' => $f['datetime'],
                    'count' => $f['count'],
                    'logtype' => $f['action'],
                    'target_rev' => $f['reverted_version'],
                    'author' => $member,
                    'ip' => $ip,
                    'contributor_uuid' => $uuid,
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