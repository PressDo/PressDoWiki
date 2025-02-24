<?php
namespace PressDo\app\Controllers\Pages\admin;

use PressDo\app\Models\{Member,ACL};
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,Config};

class Grant extends Controller
{
    public function makeData(): array
    {
        $perms = [];

        if(!empty($this->session['member']))
            ACL::getAccountPerms($this->session['uuid'], $this->session['member']['username'], $perms);

        if(!in_array('grant', $perms) && !in_array('developer', $perms)){
            $error = ['code' => 'no_permission'];
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;
        }

        $page = [
            'view_name' => 'grant',
            'title' => Languages::get('page', 'grant'),
            'data' => [],
            'menus' => [],
            'customData' => []
        ];

        if(!isset($_GET['username']))
            return $page;

        $m = Member::exist($_GET['username']);
        if (!$m) {
            $page['data'] = ['errbox' => 'err_wrong_username'];
            return $page;
        }

        $page['data']['perms'] = Config::get('wiki.permissions') ?? [
            'delete_thread',
            'admin',
            'update_thread_status',
            'nsacl',
            'hide_thread_comment',
            'grant',
            //'disable_two_factor_login',
            'login_history',
            'update_thread_document',
            'update_thread_topic',
            'hide_revision',
            'mark_troll_revision',
            'aclgroup',
            'api_access',
            'no_force_captcha',
            'batch_revert',
            'hide_document_history_log',
            'developer'
        ];
        $page['data']['have'] = array_intersect($page['data']['perms'], $perms);
        $page['data']['username'] = $m['username'];

        if(is_array($_POST['permissions'])){
            $ch_perms = [];
            foreach($_POST['permissions'] as $perm){
                if(in_array($perm, $page['data']['perms']))
                    array_push($ch_perms, $perm);
            }
            $ch_perms = array_unique($ch_perms);

            if(array_diff($ch_perms, $page['data']['have']) == array_diff($page['data']['have'], $ch_perms)){
                $page['data']['errbox'] = 'no_change';
                return $page;
            }

            $minus = array_map(fn($a) => '-'.$a, array_diff($page['data']['have'], $ch_perms));
            $plus = array_map(fn($a) => '+'.$a, array_diff($ch_perms, $page['data']['have']));
            $granted = implode(' ',$plus + $minus);
            Member::grantPermissions($this->session['member']['uuid'], $m['uuid'], $ch_perms, $granted);
            Header('Location: '.$_SERVER['REQUEST_URI']);
        }
        return $page;
    }
}