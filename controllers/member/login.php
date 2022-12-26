<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/login.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        if(!empty($this->session->member))
            Header('Location: /');
            
        if(isset($this->post->username) && isset($this->post->password)){
            $l = Models::login($this->post->username, $this->post->password, $_SERVER['REQUEST_TIME'], $this->session->ip, $this->server->HTTP_USER_AGENT);
            if(!$l)
                $error = 'err_invalid_member';
            else{
                $menus = [];
                $SP = ['aclgroup', 'grant', 'login_history'];
                $link = ['aclgroup' => '/aclgroup', 'grant' => '/admin/grant', 'login_history' => '/admin/login_history'];
                $sps = Models::special_perms($l['username']);
                foreach ($SP as $prm){
                    if(in_array($prm, $sps))
                        array_push($menus, ['l' => $link[$prm], 't' => $prm]);
                }
                $this->session->menus = $menus;
                $error = null;
                        
                $this->session->member = (object) [
                    'user_document_discuss' => null,
                    'username' => $l['username'],
                    'gravatar_url' => $l['gravatar_url'],
                    'admin' => (in_array('admin', $sps))? true:false
                ];
                
                
                
                // 로그인 성공
                if(!empty($this->uri_data->query->redirect))
                    Header('Location: '.$this->uri_data->query->redirect);
                else
                    Header('Location: /');
            }
        }else{
            $error = null;
        }

        $page = [
            'view_name' => '',
            'title' => Lang::get('page')['login'],
            'data' => [
                'error' => $error,
                'redirect' => $this->uri_data->query->redirect
            ],
            'menus' => [],
            'customData' => []
        ];
        
        return $page;
    }
}