<?php
namespace PressDo\App\Controllers\Pages\Member;

use PressDo\App\Models\Member;
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Config};

class RecoverPassword extends Controller
{
    public function makeData(): array
    {
        if(!empty($this->session['member']))
            Header('Location: /');

        $page = [
            'view_name' => 'recover_password',
            'title' => Languages::get('page')['recover_password'],
            'data' => [
                'error' => null,
                'step' => 0
            ],
            'menus' => [],
            'customData' => []
        ];

        if (isset($_POST['email']) && empty($_POST['username'])) {
            if (!self::validateCaptcha($_POST[$this->api_config['captcha_token_name']])) {
                $page['data']['error'] = 'captcha_failed';
                return $page;
            }

            if ($mem = Member::exist(email: $_POST['email'])) {
                $lang = Languages::get('mail');
                $content = $lang['greeting'].$lang['recover'].'<br><br>'.$lang['request_ip'];

                $code = Member::regCodeAdd($_POST['email'], $this->session['ip']);
                $body = sprintf($content, Config::get('wiki.site_name'), Config::get('wiki.canonical_url').'/member/recover_password?x='.$code, $this->session['ip'], $mem['username']);
                $send = self::sendMail($_POST['email'], sprintf($lang['recover_title'], Config::get('wiki.site_name'), $mem['username']) ,$body);
                
                // error in mailserver
                if(!$send)
                    $page['data']['error'] = 'err_mail_not_send';
            }
            $page['data']['step'] = 1;
            
        } elseif (!empty($_GET['x']) && empty($_POST['password'])){
            // check mail
            if ($email = Member::regCodeCheck($_GET['x'], $this->session['ip'], false)) {
                $this->session['email'] = $email;
                $page['data']['step'] = 2;
            } else {
                $page = [
                    'view_name' => 'error',
                    'title' => Languages::get('page')['error'],
                    'data' => [
                        'code' => 'err_invalid_code',
                        'errbox' => false
                    ]
                ];
                return $page;
            }
        } elseif (!empty($_POST['password']) && !empty($this->session['email'])) {
            // end recover
            $page['data']['step'] = 2;
            if($_POST['password'] !== $_POST['password2']){
                $page['data']['error'] = 'err_wrong_password2';
            }else{
                if(!empty($this->session['email'])){
                    Member::updatePassword($this->session['email'], $_POST['password']);
                    Member::regCodeUnset($this->session['email']);
                    unset($this->session['email']);
                    $_SESSION = $this->session;
                    Header('Location: /member/login');
                    exit;
                }
            }
        }
        
        return $page;
    }
}