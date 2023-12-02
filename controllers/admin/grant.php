<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/admin/grant.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        if(!WikiACL::check_perms('grant', $this->session)){
            $this->error = (object) ['code' => 'no_permission'];
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;
        }

        $page = [
            'view_name' => 'grant',
            'title' => Lang::get('page')['grant'],
            'data' => [],
            'menus' => [],
            'customData' => []
        ];

        if(!isset($this->uri_data->query->username))
            return $page;

        $m = Models::member_exist($this->uri_data->query->username);
        if(!$m){
            $page['data'] = ['errbox' => 'err_wrong_username'];
            return $page;
        }

        $page['data']['perms'] = ['delete_thread','admin','update_thread_status','nsacl','hide_thread_comment','grant','disable_two_factor_login','login_history','update_thread_document','update_thread_topic','aclgroup','api_access','no_force_captcha'];
        $page['data']['have'] = Models::special_perms($this->uri_data->query->username);
        $page['data']['username'] = $m;

        if(is_array($_POST['permissions'])){
            $perms = [];
            foreach($_POST['permissions'] as $perm){
                if(in_array($perm, $page['data']['perms']))
                    array_push($perms, $perm);
            }
            $perms = array_unique($perms);

            if(array_diff($perms, $page['data']['have']) == array_diff($page['data']['have'], $perms)){
                $page['data']['errbox'] = 'no_change';
                return $page;
            }

            $minus = array_map(fn($a) => '-'.$a, array_diff($page['data']['have'], $perms));
            $plus = array_map(fn($a) => '+'.$a, array_diff($perms, $page['data']['have']));
            $granted = implode(' ',$plus + $minus);
            Models::grant_user($this->session->member->username, $m, $perms, $granted);
            Header('Location: '.$_SERVER['REQUEST_URI']);
        }
        return $page;
    }
}