<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/thread.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        $info = Models::get_thread_info($this->uri_data->title);
        list($rawns, $namespace, $title) = [$info['namespace'], Namespaces::get($info['namespace']), $info['title']];

        $ACL = new WikiACL($rawns, $title, 'read', $this->session, $this->error);
        $ACL->check();

        if ($this->error->code == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;
        }

        $actions = ['create_thread', 'write_thread_comment'];
        $com = Models::get_comments($this->uri_data->title);
        $threads = [];
        $d_perms = [];

        $doctitle = self::make_title($rawns, $title);
           
        foreach ($com as $c){
            if($c['type'] == 'status' || $c['type'] == 'topic' || $c['type'] == 'document')
                $cont = $c['content'];
            else{
                $cont = $this::readSyntax($c['content'], Config::get('mark'), [
                    'title' => $this->page->title,
                    'thread' => true,
                    'db' => DB::getInstance(),
                    'namespace' => $this->namespaces
                ]);
            }

            $contr = explode(':', $c['contributor']);
            if($contr[0] == 'm'){
                $author = $contr[1];
                $ip = null;
            }else{
                $ip = $contr[1];
                $author = null;
            }
            $target_session = (object) [
                'menus' => [],
                'member' => [
                    'user_document_discuss' => null,
                    'username' => $author,
                    'gravatar_url' => null
                ],
                'ip' => $ip,
                'ua' => null
            ];
            $blocked = ($c['hide_author'])? true:false;

            array_push($threads, [
                'id' => $c['no'],
                'author' => $author,
                'ip' => $ip,
                'text' => $cont,
                'date' => $c['datetime'],
                'hide_author' => $c['blind'],
                'type' => $c['type'],
                'admin' => WikiACL::check_perms('admin', $target_session),
                'blocked' => $blocked
            ]);
        }
        $page = [
            'view_name' => 'thread',
            'title' => $doctitle,
            'subtitle' => Lang::get('page')['thread'],
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title
                ],
                'status' => $info['status'],
                'topic'=> $info['topic'],
                'slug' => $this->uri_data->title,
                'initial_author' => explode(':', $info['contributor'])[1],
                'comments' => $threads,
                'perms' => $d_perms,
                'updateThreadDocument' => WikiACL::check_perms('update_thread_document', $this->session),
                'updateThreadTopic' => WikiACL::check_perms('update_thread_topic', $this->session),
                'updateThreadStatus' => WikiACL::check_perms('update_thread_status', $this->session),
                'hideThreadComment' => WikiACL::check_perms('hide_thread_comment', $this->session),
                'deleteThread' => WikiACL::check_perms('delete_thread', $this->session)
            ]
        ];
        return $page;
    }
}