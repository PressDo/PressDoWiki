<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,Config};

class Signup extends Controller
{
    public function makeData(): array
    {
        if(!empty($this->session['member']))
            Header('Location: /');

        $step = 0;

        if (isset($_POST['email']) && empty($_POST['username'])) {
            $host = substr($_POST['email'],strrpos($_POST['email'], '@') + 1);

            $weh = Config::get('member.whitelist_email_host');
            if (is_string($weh) || is_array($weh) && in_array($host, $weh)) {
                $lang = Languages::get('mail');
                $content = $lang['signup'];
                
                if (Member::exist(email: $_POST['email']) !== false) {
                    $content .= '<br>'.$lang['signup_duplicate']
                        .'<br><br>'.$lang['request_ip'];
                } else {
                    $content .= '<br>'.$lang['signup_success']
                        .'<br>'.$lang['request_ip'];
                }

                $update = Member::chkEmailInput($_POST['email']);
                // already sent (in 24h)
                if (is_string($update))
                    $error = $update;
                else {
                    $code = Member::regCodeAdd($_POST['email'], $this->session['ip'], $update);
                    $body = sprintf($content, Config::get('wiki.site_name'), Config::get('wiki.canonical_url').'/member/signup?x='.$code, $this->session['ip']);

                    $send = self::sendMail($_POST['email'], sprintf($lang['signup_title'], Config::get('wiki.site_name')) ,$body);
                    $step = 1;

                    // error in mailserver
                    if(!$send)
                        $error = 'err_mail_not_send';
                }
            } else {
                $error = 'err_mail_whitelist';
            }
        } elseif (!empty($_GET['x']) && empty($_POST['username']) ){
            if ($email = Member::regCodeCheck($_GET['x'], $this->session['ip'])) {
                if ($email == 'err_ip_differs') {
                    $page = [
                        'view_name' => 'error',
                        'title' => Languages::get('page')['error'],
                        'data' => [
                            'code' => 'err_ip_differs',
                            'errbox' => false
                        ]
                    ];
                    return $page;
                }
                $this->session['email'] = $email;
                $step = 2;
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
        }elseif(!empty($_POST['username']) && !empty($_POST['password']) && !empty($this->session['email'])){
            $step = 2;
            if(!preg_match('/^[0-9A-Za-z_]+$/', $_POST['username'])){
                $error = 'err_invalid_username';
            }elseif($_POST['password'] !== $_POST['password2']){
                $error = 'err_wrong_password2';
            }else{
                if(!empty($this->session['email'])){
                    Member::register($_POST['username'], $_POST['password'], $this->session['email']);
                    $step = 3;
                }
            }
        }

        $page = [
            'view_name' => 'signup',
            'title' => Languages::get('page')['signup'],
            'data' => [
                'error' => $error,
                'step' => $step,
                'redirect' => $_GET['redirect']? base64_decode($_GET['redirect']):null
            ],
            'menus' => [],
            'customData' => []
        ];
        
        return $page;
    }
}