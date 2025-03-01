<?php
namespace PressDo\App\Controllers\Pages\Member;

use PressDo\App\Models\Member;
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Config};
use OTPHP\TOTP;

class DeactivateOtp extends Controller
{
    public function makeData()
    {
        if(!$this->session['member']){
            Header('Location: /member/login?redirect='.$_SERVER['REQUEST_URI']);
            exit;
        }

        $user = Member::getUserInfo($this->session['uuid']);

        if(empty($user['totp_secret'])){
            $error = ['code' => 'already_activated_otp'];
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page')['error'],
                'data' => $error
            ];
            return $page;
        }

        if (!empty($_POST['pin'])) {
            $otp = TOTP::createFromSecret($this->session['otp_secret']);

            if ($otp->verify($_POST['pin'])) {
                Member::removeTotp($this->session['uuid']);
                header('Location: /member/mypage');
            } else
                $errmsg = 'err_invalid_pin';
        }

        $page = [
            'view_name' => 'deactivate_otp',
            'title' => Languages::get('page')['deactivate_otp'],
            'data' => [
                'error' => $errmsg,
                'redirect' => base64_decode($_GET['redirect'])
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}