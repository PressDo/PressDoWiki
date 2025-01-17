<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/login.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data(): array
    {
        if(in_array($this->post->email, Config::get('member')['whitelist_email_host'])){
            
        }else{
            $error = 'err_mail_whitelist';
        }

        $page = [
            'view_name' => 'signup',
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