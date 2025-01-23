<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/mypage.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data(): array
    {
        if(!$this->session->member){
            Header('Location: /member/login?redirect='.$this->server->REQUEST_URI);
            exit;
        }

        $user = Models::get_user_info($this->session->member->uuid);
        $webauthn = Models::get_user_webauthn($this->session->member->uuid);

        $dirs = scandir('skins');
        if(count($dirs) == 2)
            $skins = [];
        else
            $skins = array_slice($dirs, 2);

        $page = [
            'view_name' => 'mypage',
            'title' => Lang::get('page')['mypage'],
            'data' => [
                'skins' => $skins,
                'totp' => ($user['totp_secret']),
                'email' => $user['email'],
                'perms' => explode(',', $user['perm']),
                'webauthn' => $webauthn
            ],
            'menus' => [],
            'customData' => []
        ];
        
        return $page;
    }
}