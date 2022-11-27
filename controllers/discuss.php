<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/discuss.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        list($rawns, $namespace, $title) = self::parse_title($this->uri_data->title);

        $ACL = new WikiACL($rawns, $title, 'read', $this->session, $this->error);
        $ACL->check();
        $d_perms = [];
        $perms = ['delete_thread', 'update_thread_status', 'hide_thread_comment', 'update_thread_document', 'update_thread_topic'];
        $actions = ['create_thread', 'write_thread_comment'];
        foreach ($perms as $p){
            if($ACL::check_perms($p, $this->session, $title) === true)
                array_push($d_perms, $p);
        }

        foreach ($actions as $a){
            $ACL = new WikiACL($rawns, $title, $a, $this->session, $this->error);
            $ACL->check();
            if ($this->error->code !== 'permission_'.$a)
            array_push($d_perms, $a);
        }

        $thr = Models::get_doc_thread($rawns,$title);
        $threads = [];
        foreach ($thr as $t){
            $com = Thread::getLatestComments($t['urlstr']);
            $discuss = [];
            foreach ($com as $c){
                if($c['type'] == 'status' || $c['type'] == 'topic' || $c['type'] == 'document')
                    $cont = $c['content'];
                else
                    $cont = $this::readSyntax($c['content'], Config::get('mark'), ['thread' => true])['html'];
                $blocked = ($c['hide_author'])? true:false;
                array_push($discuss, [
                    'id' => $c['no'],
                    'author' => $c['contributor_m'],
                    'ip' => $c['contributor_i'],
                    'text' => $cont,
                    'date' => $c['datetime'],
                    'hide_author' => $c['blind'],
                    'type' => $c['type'],
                    'admin' => $ACL::check_perms('admin',$this->session,$title),
                    'blocked' => $blocked
                ]);
            }
            $ra = [
                'slug' => $t['urlstr'],
                'topic' => $t['topic'],
                'discuss' => $discuss
            ];
            array_push($threads, $ra);
        }
        $page = [
            'view_name' => 'discuss',
            'title' => $this->uri_data->title.' ('.Lang::get('page')['discuss'].')',
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title
                ],
                'thread_list' => $threads,
                'editRequests' => [
                    'slug'
                ],
                'perms' => $d_perms
            ]
        ];
        return $page;
    }
}