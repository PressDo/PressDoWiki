<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/wiki.php';
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
        $page = [
            'view_name' => 'wiki',
            'title' => $this->uri_data->title,
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
            return $page;
        }

        $doc = Models::load($rawns, $title, $this->uri_data->rev);

        # If Not Found
        if($doc === null){
            $page = [
                'view_name' => 'notfound',
                'full_title' => $this->uri_data->title,
                'data' => [
                    'document' => [
                        'namespace' => $namespace,
                        'title' => $title,
                    ],
                    'discuss_progress' => false,
                    'user' => ($namespace == Namespaces::get('user')),
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
            $discussions = Models::get_doc_thread($rawns,$title);
            $docid = Models::get_doc_id($rawns, $title);

            $content = $this::readSyntax($doc['content'], Config::get('mark'), [
                'title' => $this->page->title, 
                'noredirect' => $this->uri_data->query->noredirect,
                'db' => DB::getInstance(),
                'namespace' => $this->namespaces
            ]);
            
            $page['data'] = [
                'starred' => $this->session->member?Models::if_starred($docid,$this->session->member->username):false,
                'star_count' => Models::count_stars($docid),
                'document' => [
                    'namespace' => $doc['namespace'],
                    'title' => $doc['title'],
                    'content' => htmlspecialchars($content['html']),
                    'categories' => $content['categories']
                ],
                'discuss_progress' => (isset($discussions[0])),
                'date' => $doc['datetime'],
                'rev' => isset($this->uri_data->rev)?$this->uri_data->rev:null,
                'user' => ($namespace == Namespaces::get('user')),
                'userData' => ($namespace == Namespaces::get('user'))?[
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