<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{Document,Thread,EditRequest};
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;
use PressDo\App\Helpers\Languages;

class Discuss extends Controller
{
    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);
        $error = [];

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');
        $d_perms = [];
        $perms = ['delete_thread', 'update_thread_status', 'hide_thread_comment', 'update_thread_document', 'update_thread_topic'];
        $actions = ['create_thread', 'write_thread_comment'];
        foreach ($perms as $p) {
            if(in_array($p, $ACL->perms))
                array_push($d_perms, $p);
        }

        foreach ($actions as $a) {
            $ACL->check($a);
            if ($error['code'] !== 'permission_'.$a)
                array_push($d_perms, $a);
        }

        if ($_GET['state'] == 'close' || $_GET['state'] == 'closed_edit_requests') {
            // 닫힌 00 목록
            if ($_GET['state'] == 'closed_edit_requests') {
                $viewname = 'edit_request_close';
                $threads = Editrequest::getBulk($uuid, 'close');
            } else {
                $viewname = 'thread_list_close';
                $threads = Thread::getDocThread($uuid, 'closed');
            }

            $page = [
                'view_name' => $viewname,
                'title' => $this->uri_data->title,
                'data' => [
                    'document' => [
                        'namespace' => $namespace,
                        'title' => $title,
                        'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                    ],
                    'state' => $_GET['state'],
                    'thread_list' => $threads,
                    'editRequests' => [
                        //'slug'
                    ],
                    'perms' => $d_perms
                ]
            ];
        } else {
            $thr = Thread::getDocThread($uuid);
            $threads = [];
            foreach ($thr as $t){
                $com = Thread::getLatestComments($t['urlstr']);
                $discuss = [];
                foreach ($com as $c){
                    if ($c['type'] == 'status' || $c['type'] == 'topic' || $c['type'] == 'document')
                        $cont = $c['content'];
                    else
                        $cont = self::readSyntax($c['content'], ['thread' => true]);
                    $blocked = ($c['hide_author']);

                    $contr = explode(':', $c['contributor']);
                    if ($contr[0] == 'm') {
                        $author = $contr[1];
                        $ip = null;
                    } else {
                        $ip = $contr[1];
                        $author = null;
                    }
                    array_push($discuss, [
                        'id' => $c['no'],
                        'author' => $author,
                        'ip' => $ip,
                        'text' => $cont,
                        'date' => $c['datetime'],
                        'hide_author' => $c['blind'],
                        'type' => $c['type'],
                        'admin' => in_array('admin',$ACL->perms),
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
                'view_name' => 'thread_list',
                'title' => $this->uri_data->title,
                'data' => [
                    'document' => [
                        'namespace' => $namespace,
                        'title' => $title,
                        'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                    ],
                    'thread_list' => $threads,
                    'edit_requests' => EditRequest::getBulk($uuid),
                    'perms' => $d_perms
                ]
            ];
        }

        return $page;
    }
}