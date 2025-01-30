<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\Languages;

class ActivateOtp extends Controller
{
    public function makeData()
    {
        if(!$this->session['member']){
            Header('Location: /member/login?redirect='.$_SERVER['REQUEST_URI']);
            exit;
        }

        $user = Member::getUserInfo($this->session['member']['uuid']);

        if($user['totp_secret'] !== null){
            $error = ['code' => 'already_activated_otp'];
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page')['error'],
                'data' => $error
            ];
            return $page;
        }

        // (pin validation 로직)
        // $error = 'pin_register_invalid'

        $page = [
            'view_name' => 'activate_otp',
            'title' => Languages::get('page')['activate_otp'],
            'data' => [
                'redirect' => base64_decode($_GET['redirect'])
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}