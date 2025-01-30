<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,History as H, Member};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\{Languages};

class History extends Controller
{
    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);
        $error = [];

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');

        $page = [
            'view_name' => 'history',
            'title' => $this->uri_data->title,
            'subtitle' => Languages::get('page', 'history'),
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
        
        if ($error['code'] == 'permission_read') {
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;
        }

        if ($uuid !== false) {
            if (isset($_GET['from']))
                $from = $_GET['from'];

            if (isset($_GET['until']))
                $until = $_GET['until'];

            $fetch = H::load($uuid, $from, $until);
            $ver = H::getVersion($uuid);
            //$l = Models::get_rev_time($namespace,$title, 1);
            $localConfig = [];
            $cn = count($fetch);
            $cl = ($cn < 31)? $cn:$cn-1;

            foreach($fetch as $f){
                if ($f['contributor_i'] !== null) {
                    $Cuuid = Document::bin2uuid($f['contributor_i']);
                    $ip = Member::ipLookup($Cuuid);
                    $member = null;
                } elseif($f['contributor_m'] !== null) {
                    $Cuuid = Document::bin2uuid($f['contributor_m']);
                    $member = Member::lookup($Cuuid);
                    $ip = null;
                }

                array_push($page['data']['history'], [
                    'rev' => $f['rev'],
                    'uuid' => Document::bin2uuid($f['uuid']),
                    'log' => $f['comment'],
                    'date' => $f['datetime'],
                    'count' => $f['count'],
                    'logtype' => $f['action'],
                    'target_rev' => $f['reverted_version'],
                    'author' => $member,
                    'ip' => $ip,
                    'contributor_uuid' => $Cuuid,
                    'style' => null,
                    'blocked' => null,
                    'edit_request' => $f['edit_request_uri'],
                    'acl' => $f['acl_changed'],
                    'from' => $f['moved_from'],
                    'to' => $f['moved_to'],
                    'user_mode' => []
                ]);
            }
            $page['data']['prev_ver'] = $fetch[0]['rev'] == $ver ? null : $fetch[0]['rev'] + 1;
            $page['data']['next_ver'] = end($fetch)['rev'] == 1 ? null : end($fetch)['rev'] - 1;
            //$page['data']['initial_date'] = $l;
        }else{
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => ['code' => 'no_such_document']
            ];
        }
        return $page;
    }
}