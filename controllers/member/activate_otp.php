<?php
namespace PressDo;

require 'controllers/common.php';
//require 'models/member/activate_otp.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        if(!$this->session->member){
            Header('Location: /member/login?redirect='.$this->server->REQUEST_URI);
            exit;
        }

        $user = Models::get_user_info($this->session->member->uuid);

        if($user['totp_secret'] !== null){
            $this->error = (object) ['code' => 'already_activated_otp'];
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => $this->error
            ];
            return $page;
        }

        // (pin validation 로직)
        // $error = 'pin_register_invalid'

        $page = [
            'view_name' => 'activate_otp',
            'title' => Lang::get('page')['activate_otp'],
            'data' => [
                'redirect' => base64_decode($this->uri_data->query->redirect)
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}