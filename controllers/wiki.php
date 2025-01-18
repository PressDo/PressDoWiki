<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/wiki.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function update_linktable(string $uuid, array $links)
    {
        if(count($links['link']) > 0 || count($links['redirect']) > 0 || count($links['include']) > 0 || count($links['file']) > 0)
            Models::update_forlinks($uuid, $links);
    }

    public function make_data(): array
    {
        list($namespace, $title) = self::parse_title($this->uri_data->title);

        $ACL = new WikiACL($namespace, $title, 'read', $this->session, $this->error);
        $ACL->check();
        $page = [
            'view_name' => 'wiki',
            'title' => $this->uri_data->title,
            'subtitle' => '',
            'data' => [
                'starred' => null,
                'star_count' => null,
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'discuss_progress' => false,
                'date' => null,
                'rev' => null,
                'user' => false,
                'userData' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if ($this->error->code == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;
        }

        # If Not Found
        if(!Models::exist($namespace,$title, $backlinkrefreshed)){
            $page = [
                'view_name' => 'notfound',
                'title' => $this->uri_data->title,
                'data' => [
                    'discuss_progress' => false,
                    'user' => ($namespace == '사용자'),
                    'menus' => [],
                    'customData' => []
                ]
            ];
            $this->error = (object) [
                'code' => 'notfound',
                'message' => 'document_not_found',
                'errbox' => false
            ];
        }else{
            # If found
            $discussions = Models::get_doc_thread($namespace,$title);
            $uuid = str_replace('-', '', Models::get_doc_uuid($namespace,$title));
            //$lver = Models::get_version($namespace,$title);
            $rev = isset($_GET['rev'])?intval($_GET['rev']):null;

            
            $doc = Models::load($namespace, $title, $rev);
            $content = $this::readSyntax($doc['content'], Config::get('mark'), [
                'title' => $this->uri_data->title,
                'noredirect' => $this->uri_data->query->noredirect,
                'db' => DB::getInstance(),
                'thread' => false
            ]);

            if(!$backlinkrefreshed)
                self::update_linktable($uuid, $content['links']);

            if($rev !== null)
                $page['subtitle'] = str_replace('@1@', $rev, Lang::get('document')['rev']);
            $page['data'] = [
                'starred' => $this->session->member ? Models::if_starred($uuid,$this->session->member->username):false,
                'star_count' => Models::count_stars($uuid),
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'content' => htmlspecialchars($content['html']),
                    'categories' => $content['categories']
                ],
                'discuss_progress' => (isset($discussions[0])),
                'date' => $doc['datetime'],
                'rev' => $rev,
                'user' => ($namespace == '사용자'),
                'userData' => ($namespace == '사용자')?[
                    'admin' => WikiACL::check_perms('admin', $this->session, $title),
                    'block' => [
                        'blocked' => false,
                        'seq',
                        'dt_html',
                        'datetime',
                        'memo',
                        'until'
                    ]
                ]:null
            ];
            //'debug' => $this->uri_data
        }
        return $page;
    }
}