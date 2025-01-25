<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/signup.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data(): array
    {
        if(!empty($this->session->member))
            Header('Location: /');

        $step = 0;
        //var_dump($this->session->email);
        if(isset($this->post->email) && empty($this->post->username)){
            $host = substr($this->post->email,strrpos($this->post->email, '@') + 1);
            if(in_array($host, Config::get('member')['whitelist_email_host'])){
                $lang = Lang::get('mail');
                $content = $lang['signup'];
                if(Models::member_exist(email: $this->post->email) !== false){
                    $content .= '<br>'.$lang['signup_duplicate']
                        .'<br><br>'.$lang['request_ip'];
                }else{
                    $content .= '<br>'.$lang['signup_success']
                        .'<br>'.$lang['request_ip'];
                }

                $update = Models::chkemailinput($this->post->email);
                // already sent (in 24h)
                if(is_string($update))
                    $error = $update;
                else{
                    $code = Models::regcodeadd($this->post->email, $this->session->ip, $update);
                    $body = str_replace(['@1@', '@2@', '@3@'], [Config::get('wiki')['site_name'], Config::get('wiki')['canonical_url'].'/member/signup?x='.$code, $this->session->ip], $content);

                    $send = self::mail_send($this->post->email, str_replace('@1@', Config::get('wiki')['site_name'], $lang['signup_title']) ,$body);
                    $step = 1;

                    // error in mailserver
                    if(!$send)
                        $error = 'err_mail_not_send';
                }
            }else{
                $error = 'err_mail_whitelist';
            }
        }elseif(!empty($this->uri_data->query->x) && empty($this->post->username)){
            if($email = Models::regcodecheck($this->uri_data->query->x, $this->session->ip)){
                if($email == 'err_ip_differs'){
                    $this->error = (object) [
                        'code' => 'err_ip_differs',
                        'errbox' => false
                    ];
                    $page = [
                        'view_name' => 'error',
                        'title' => Lang::get('page')['error'],
                        'data' => (array) $this->error
                    ];
                    return $page;
                }
                $this->session->email = $email;
                $step = 2;
            }else{
                $this->error = (object) [
                    'code' => 'err_invalid_code',
                    'errbox' => false
                ];
                $page = [
                    'view_name' => 'error',
                    'title' => Lang::get('page')['error'],
                    'data' => (array) $this->error
                ];
                return $page;
            }
        }elseif(!empty($this->post->username) && !empty($this->post->password) && !empty($this->session->email)){
            $step = 2;
            if(!preg_match('/^[0-9A-Za-z_]+$/', $this->post->username)){
                $error = 'err_invalid_username';
            }elseif($this->post->password !== $this->post->password2){
                $error = 'err_wrong_password2';
            }else{
                if(!empty($this->session->email)){
                    Models::register_user($this->post->username, $this->post->password, $this->session->email);
                    $step = 3;
                }
            }
        }

        $page = [
            'view_name' => 'signup',
            'title' => Lang::get('page')['signup'],
            'data' => [
                'error' => $error,
                'step' => $step,
                'redirect' => $this->uri_data->query->redirect? base64_decode($this->uri_data->query->redirect):null
            ],
            'menus' => [],
            'customData' => []
        ];
        
        return $page;
    }
}