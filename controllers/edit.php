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
        
        $ACL = new WikiACL($rawns, $title, 'edit', $this->session, $this->error);
        $ACL->check();
        
        $page = [
            'view_name' => 'edit',
            'title' => $this->uri_data->title.' ('.Lang::get('page')['edit'].')',
            'data' => [
                'editor' => [
                    'baserev' => null,
                    'section' => null,
                    'raw' => null
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
        }

        $doc = Models::load($rawns, $title, $this->uri_data->rev);
        
        $page['data']['editor']['baserev'] = Models::get_version($rawns, $title) ?? 0;
        $page['data']['editor']['raw'] = $doc['content'] ?? null;
        $page['data']['editor']['section'] = $this->uri_data->query->section;

        $page['data']['token'] = self::rand(64);
        

        return $page;
    }
}