<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/blank.php';
require 'controllers/lib/libacl.php';

use PressDo\{Models,WikiACL};
class WikiPage extends WikiCore
{
    public function make_data()
    {
        [$namespace, $title] = self::parse_title($this->uri_data->title);
        $uuid = Models::get_doc_uuid($namespace, $title, $backlinkrefreshed);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $this->error);
        $ACL->check('read');
        $page = [
            'view_name' => 'raw',
            'title' => $this->uri_data->title,
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'rev' => null,
                'text' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if ($this->error->code == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => $this->error
            ];
            return $page;
        }

        if(!$uuid){
            $this->error = (object) ['code' => 'no_such_document'];
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
        }else{
            $rev = $this->uri_data->query->uuid ?? $uuid;
            $doc = Models::load($uuid, $rev);

            if($doc === null){
                $this->error = (object) ['code' => 'no_such_revision'];
                $page = [
                    'view_name' => 'error',
                    'title' => Lang::get('page')['error'],
                    'data' => (array) $this->error
                ];
                return $page;
            }

            $page['subtitle'] .= 'r'.$doc['rev'].' RAW';
            $page['data']['rev'] = $doc['rev'];
            $page['data']['text'] = $doc['content'];
            //'debug' => $this->uri_data
        }
        return $page;
    }
}