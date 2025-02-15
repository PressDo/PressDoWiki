<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,Thread as T,Member};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\{Languages,Database};

class Thread extends Controller
{
    public function makeData(): array
    {
        $info = T::getInfo($this->uri_data->title);
        $uuid = $info['document'];
        ['namespace' => $namespace, 'title' => $title] = Document::getTitleByUuid($uuid);
        $error = [];

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');

        if ($error['code'] == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page')['error'],
                'data' => $error
            ];
            return $page;
        }

        $actions = ['create_thread', 'write_thread_comment'];
        $com = T::getComments($this->uri_data->title);
        $threads = [];
        $d_perms = [];

        $doctitle = self::makeTitle($namespace, $title);
           
        foreach ($com as $c){
            if ($c['type'] == 'status' || $c['type'] == 'topic' || $c['type'] == 'document')
                $cont = $c['content'];
            else {
                $cont = $this::readSyntax($c['content'], [
                    'title' => $this->uri_data->title,
                    'thread' => true
                ]);
            }

            if ($c['contributor_i'] !== null) {
                $cuuid = Document::bin2uuid($c['contributor_i']);
                $ip = Member::ipLookup($cuuid);
                $author = null;
            } elseif($c['contributor_m'] !== null) {
                $cuuid = Document::bin2uuid($c['contributor_m']);
                $author = Member::lookup($cuuid);
                $ip = null;

                $sps = Member::specialPerms($cuuid);
            }

            $blocked = ($c['hide_author']);

            array_push($threads, [
                'id' => $c['no'],
                'author' => $author,
                'ip' => $ip,
                'contributor_uuid' => $cuuid,
                'text' => $cont,
                'date' => $c['datetime'],
                'hide_author' => $c['blind'],
                'type' => $c['type'],
                'admin' => in_array('admin', $sps),
                'blocked' => $blocked
            ]);
        }

        $init_c = $info['init_m'] ?? $info['init_i'];

        $page = [
            'view_name' => 'thread',
            'title' => $doctitle,
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                ],
                'status' => $info['status'],
                'topic'=> $info['topic'],
                'slug' => $this->uri_data->title,
                'initial_author' => Document::bin2uuid($init_c),
                'comments' => $threads,
                'perms' => $d_perms,
                'updateThreadDocument' => in_array('update_thread_document', $sps),
                'updateThreadTopic' => in_array('update_thread_topic', $sps),
                'updateThreadStatus' => in_array('update_thread_status', $sps),
                'hideThreadComment' => in_array('hide_thread_comment', $sps),
                'deleteThread' => in_array('delete_thread', $sps)
            ]
        ];
        return $page;
    }
}