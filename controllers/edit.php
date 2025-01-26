<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/edit.php';
require 'controllers/lib/libacl.php';

use PressDo\{Models,WikiACL};
class WikiPage extends WikICore
{
    public function make_data()
    {
        [$namespace, $title] = self::parse_title($this->uri_data->title);
        if(!$this->error) $this->error = null;
        $uuid = Models::get_doc_uuid($namespace, $title, $backlinkrefreshed);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $this->error);
        $ACL->check('read');
        if ($this->error->code == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;//$this::make_error();
        }
        $ACL->check('edit');

        // 편집권한이 없으면 편집 요청 권한 확인
        if ($this->error?->code == 'permission_edit'){
            $error = $this->error;
            $ACL->check('edit_request');
            if ($this->error->code !== 'permission_edit_request')
                Header('Location: /edit_request/'.$this->uri_data->title);
            else
                $this->error = $error;
        }

        // Edit Submission
        if(isset($this->post->token) && isset($this->post->content)){
            $this->post->content = htmlspecialchars_decode($this->post->content);
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
                $member = $this->session->member->uuid ?? null;
                $ip = (!$member? $this->session->ip : null);

                if(!$uuid){
                    $uuid = Models::create_document($namespace, $title);
                    $action = 'create';
                }
                
                Models::save_document(
                    $uuid,
                    $this->post->content,
                    $this->post->comment,
                    $member,
                    $ip,
                    $this->session->baserev,
                    iconv_strlen($this->session->raw),
                    $action ?? 'modify'
                );
                

                Header('Location: /w/'.$this->uri_data->title);
            }
        }

        $doc = Models::load($uuid);

        $this->session->baserev = ($uuid) ? Models::get_version($uuid) : 0;
        $this->session->raw = ($uuid) ? $doc['content'] : '';
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
                'user' => ($namespace == '사용자'),
                'token' => self::rand(64)
            //   'customData' => $ad_set
            ]
        ];

        $this->session->token = $page['data']['token'];
        return $page;
    }
}