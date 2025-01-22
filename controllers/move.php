<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/move.php';
require 'controllers/lib/libacl.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        [$namespace, $title] = self::parse_title($this->uri_data->title);
        $uuid = Models::get_doc_uuid($namespace, $title, $backlinkrefreshed);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $this->error);
        $ACL->check('move');
        $page = [
            'view_name' => 'move',
            'title' => $this->uri_data->title,
            'subtitle' => Lang::get('page')['move'],
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'captcha' => null,
                'token' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if ($this->error->code == 'permission_read' || $this->error->code == 'permission_edit' || $this->error->code == 'permission_move'){
            return $page;
        }

        // 문서 없음
        if(!$uuid){
            $this->error = (object) ['code' => 'no_such_document'];
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;
        }

        if(isset($this->post->token) && $this->session->token !== $this->post->token){
            $this->error = (object) [
                'code' => 'err_csrf_token',
                'errbox' => true
            ];
        }elseif(isset($this->post->token) && $this->session->token == $this->post->token && isset($this->post->new_title)){
            if(!empty($this->session->member)){
                $id = 'm:'.$this->session->member->username;
            }else{
                $id = 'i:'.$this->session->ip;
            }
            Models::move_document($uuid, $this->uri_data->title, $this->post->new_title, $id, $this->post->summary);
            Header('Location: /w/'.$this->post->new_title);
        }else{
            $this->session->token = self::rand(64);
            $page['data']['token'] = $this->session->token;
        }

        return $page;
    }
}