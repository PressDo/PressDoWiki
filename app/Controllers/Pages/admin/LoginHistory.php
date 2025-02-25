<?php
namespace PressDo\app\Controllers\Pages\admin;

use PressDo\app\Models\{Member,ACL};
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,Config};

class LoginHistory extends Controller
{
    public function makeData(): array
    {
        $perms = [];

        if(!empty($this->session['member']))
            ACL::getAccountPerms($this->session['uuid'], $this->session['member']['username'], $perms);

        if(!in_array('login_history', $perms) && !in_array('developer', $perms)) {
            $error = ['code' => 'no_permission'];
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;
        }

        $page = [
            'view_name' => 'login_history',
            'title' => Languages::get('page', 'login_history'),
            'data' => [],
            'menus' => [],
            'customData' => []
        ];

        if(empty($_GET['username']))
            return $page;

        $m = Member::exist($_GET['username']);
        if (!$m) {
            $page['data'] = ['errbox' => 'err_wrong_username'];
            return $page;
        }

        if (in_array('hideip', Member::specialPerms($m['uuid'])) && !in_array('developer', $perms)) {
            $page['data'] = ['errbox' => 'invalid_permission'];
            return $page;
        }

        $page['data']['username'] = $m['username'];
        
    
        $dat = Member::loginHistory($m['uuid'], $this->session['member']['uuid'],$_GET['from'],$_GET['until']);
        $page['data'] = [
            'from' => null,
            'until' => null,
            'UA' => $m['last_login_ua'],
            'email' => $m['email'],
            'history' => []
        ];

        foreach ($dat as $h){
            array_push($page['data']['history'], [
                'datetime' => $h['datetime'],
                'ip' => $h['ip']
            ]);
        }

        $lt = Member::getLogintimeEnd($m['uuid']);

        if($page['data']['history'][0]['datetime'] != $lt['max'])
            $page['data']['until'] = $page['data']['history'][0]['datetime'] + 1;

        if (end($page['data']['history'])['datetime'] != $lt['min'])
            $page['data']['from'] = end($page['data']['history'])['datetime'] - 1;

        return $page;
    }
}