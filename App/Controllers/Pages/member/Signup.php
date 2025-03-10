<?php
namespace PressDo\App\Controllers\Pages\Member;

use PressDo\App\Models\Member;
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Config};

class Signup extends Controller
{
    public function makeData(): array
    {
        if(!empty($this->session['member']))
            Header('Location: /');

        $page = [
            'view_name' => 'signup',
            'title' => Languages::get('page')['signup'],
            'data' => [
                'error' => null,
                'step' => 0,
                'redirect' => $_GET['redirect']? base64_decode($_GET['redirect']):null
            ],
            'menus' => [],
            'customData' => []
        ];

        if (isset($_POST['email']) && empty($_POST['username'])) {
            // send email
            $host = substr($_POST['email'],strrpos($_POST['email'], '@') + 1);

            $weh = Config::get('member.whitelist_email_host');
            if (is_string($weh) && $weh == $host || is_array($weh) && in_array($host, $weh)) {
                if (!self::validateCaptcha($_POST[$this->api_config['captcha_token_name']])) {
                    $page['data']['error'] = 'captcha_failed';
                    return $page;
                }
                $lang = Languages::get('mail');
                $content = $lang['greeting'].$lang['signup'];
                
                if (Member::exist(email: $_POST['email']) !== false) {
                    $duplicatesignup = true;
                    $content .= '<br>'.$lang['signup_duplicate']
                        .'<br><br>'.$lang['request_ip'];
                } else {
                    $content .= '<br>'.$lang['signup_success']
                        .'<br>'.$lang['request_ip'];
                }

                $update = Member::chkEmailInput($_POST['email']);
                // already sent (in 24h)
                if (is_string($update))
                    $page['data']['error'] = $update;
                else {
                    if (!$duplicatesignup)
                        $code = Member::regCodeAdd($_POST['email'], $this->session['ip']);
                    $body = sprintf($content, Config::get('wiki.site_name'), Config::get('wiki.canonical_url').'/member/signup?x='.$code, $this->session['ip']);

                    $send = self::sendMail($_POST['email'], sprintf($lang['signup_title'], Config::get('wiki.site_name')) ,$body);
                    $page['data']['step'] = 1;

                    // error in mailserver
                    if(!$send)
                        $page['data']['error'] = 'err_mail_not_send';
                }
            } else {
                $page['data']['error'] = 'err_mail_whitelist';
            }
        } elseif (!empty($_GET['x']) && empty($_POST['username']) ){
            // check email
            if ($email = Member::regCodeCheck($_GET['x'], $this->session['ip'], true)) {
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
        } elseif (!empty($_POST['username']) && !empty($_POST['password']) && !empty($this->session['email'])){
            // end register
            $page['data']['step'] = 2;
            if(!preg_match('/^[0-9A-Za-z_]+$/', $_POST['username'])){
                $page['data']['error'] = 'err_invalid_username';
            }elseif($_POST['password'] !== $_POST['password2']){
                $page['data']['error'] = 'err_wrong_password2';
            }else{
                if(!empty($this->session['email'])){
                    Member::register($_POST['username'], $_POST['password'], $this->session['email']);
                    $page['data']['step'] = 3;
                }
            }
        }
        
        return $page;
    }
}