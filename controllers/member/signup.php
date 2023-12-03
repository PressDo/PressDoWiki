<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/login.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        if(isset($this->post->username) && isset($this->post->password)){
            $l = Models::login($this->post->username, password_hash($this->post->password, PASSWORD_BCRYPT), $_SERVER['REQUEST_TIME'], $this->session->ip, $this->server->HTTP_USER_AGENT);
            if(!$l)
                $error = 'err_invalid_member';
            else{
                $menus = [];
                $SP = ['aclgroup', 'grant', 'login_history'];
                $sps = Models::special_perms($l['username']);
                foreach ($SP as $prm){
                    if(in_array($prm, $sps))
                        array_push($menus, $prm);
                }
                $this->session->menus = $menus;
                $error = null;
                        
                $this->session->member = (object) [
                    'user_document_discuss' => null,
                    'username' => $l['username'],
                    'gravatar_url' => $l['gravatar_url']
                ];
            }
        }else{
            $error = null;
        }

        $page = [
            'view_name' => '',
            'title' => Lang::get('page')['signup'],
            'data' => [
                'error' => $error,
                'redirect' => base64_decode($this->uri_data->query->redirect)
            ],
            'menus' => [],
            'customData' => []
        ];
        
        return $page;
    }
}