<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\Languages;

class Login extends Controller
{
    public function makeData()
    {
        if(!empty($this->session['member']))
            Header('Location: /');

        $page = [
            'view_name' => '',
            'title' => Languages::get('page')['login'],
            'data' => [
                'redirect' => $_GET['redirect']
            ],
            'menus' => [],
            'customData' => []
        ];
            
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $l = Member::login($_POST['username'], $_POST['password'], $_SERVER['REQUEST_TIME'], $this->session['ip'], $_SERVER['HTTP_USER_AGENT']);
            if (!$l)
                $page['data']['error'] = 'err_invalid_member';
            else {
                $menus = [];
                $SP = ['aclgroup', 'grant', 'login_history'];
                $link = ['aclgroup' => '/aclgroup', 'grant' => '/admin/grant', 'login_history' => '/admin/login_history'];
                $sps = Member::specialPerms($l['uuid']);
                foreach ($SP as $prm) {
                    if (in_array($prm, $sps))
                        array_push($menus, ['l' => $link[$prm], 't' => $prm]);
                }
                $this->session['menus'] = $menus;
                        
                $this->session['member'] = [
                    'user_document_discuss' => null,
                    'uuid' => $l['uuid'],
                    'username' => $l['username'],
                    'gravatar_url' => '//www.gravatar.com/avatar/'.md5($l['email']).'?d=retro',
                    'admin' => in_array('admin', $sps),
                    'settings' => ['skin' => $l['skin']]
                ];
                
                // 로그인 성공
                if (!empty($_GET['redirect']))
                    Header('Location: '.$_GET['redirect']);
                else
                    Header('Location: /');
            }
        }
        return $page;
    }
}