<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/edit.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikICore
{
    public function make_data()
    {
        list($rawns, $namespace, $title) = self::parse_title($this->uri_data->title);
        if(!$this->error) $this->error = null;
        $exist = Models::exist($rawns,$title);

        $ACL = new WikiACL($rawns, $title, 'edit', $this->session, $this->error);
        $ACL->check();

        // Edit Submission
        if(isset($this->post->token) && isset($this->post->content)){
            if($this->post->token !== $this->session->token){
                // Reject: wrong anti-CSRF token
                $this->error = (object) [
                    'code' => 'err_csrf_token',
                    'errbox' => true
                ];
            }elseif($this->session->raw == $this->post->content){
                // Reject: same doc content
                $this->error = (object) [
                    'code' => 'err_same_content',
                    'errbox' => true
                ];
            }else{
                // Approve Edit
                if(!empty($this->session->member)){
                    $id = 'm:'.$this->session->member->username;
                }else{
                    $id = 'i:'.$this->session->ip;
                }

                if($exist){
                    Models::save_document($rawns,$title,$this->post->content,$this->post->comment,$id,$this->session->baserev,iconv_strlen($this->session->raw));
                }else{
                    Models::create_document($rawns,$title,$this->post->content,$this->post->comment,$id);
                }

                Header('Location: /w/'.$this->uri_data->title);
            }
        }

        $doc = Models::load($rawns, $title, $this->uri_data->query->rev);

        $this->session->baserev = ($exist) ? Models::get_version($rawns, $title) : 0;
        $this->session->raw = ($exist) ? $doc['content'] : '';
        $section = $this->uri_data->query->section;

        $page = [
            'view_name' => 'edit',
            'title' => $this->uri_data->title,
            'subtitle' => 'r'.$this->session->baserev.' '.Lang::get('page')['edit'],
            'data' => [
                'editor' => [
                    'baserev' => $this->session->baserev,
                    'section' => $section,
                    'raw' => $this->session->raw
                ],
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'ForceShowNameSpace' => Config::get('ForceShowNameSpace')
                ],
                'user' => ($namespace == Namespaces::get('user')),
                'token' => null
            //   'customData' => $ad_set
            ]
        ];

        // 편집권한이 없으면 편집 요청 권한 확인
        if ($this->error && ($this->error->code == 'permission_read' || $this->error->code == 'permission_edit')){
            $ACL = new WikiACL($rawns, $title, 'edit_request', $this->session, $this->error);
            $ACL->check();
            if ($this->error->code == 'permission_edit_request')
                return $page;
            else
                Header('Location: /edit_request/'.$this->uri_data->titleurl);
        }elseif($this->error){
            return $page;
        }

        $this->session->token = $page['data']['token'] = self::rand(64);

        return $page;
    }
}