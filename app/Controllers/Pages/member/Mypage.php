<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\Languages;

class Mypage extends Controller
{
    public function makeData(): array
    {
        if(!$this->session['member']){
            Header('Location: /member/login?redirect='.$_SERVER['REQUEST_URI']);
            exit;
        }

        $user = Member::getUserInfo($this->session['member']['uuid']);
        $webauthn = Member::getUserWebauthn($this->session['member']['uuid']);

        $dirs = scandir('skins');
        if (count($dirs) == 2)
            $skins = [];
        else
            $skins = array_slice($dirs, 2);

        $page = [
            'view_name' => 'mypage',
            'title' => Languages::get('page')['mypage'],
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