<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/blank.php';
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

        if(Models::exist($rawns,$title)){
            $lver = Models::get_version($rawns,$title);
            if(!$this->uri_data->query->rev)
                $rev = $lver;
            else
                $rev = $this->uri_data->query->rev;

            if(!is_numeric($rev) || $rev > $lver || $rev < 1){
                $this->error = (object) ['code' => 'no_such_revision'];
                $page = [
                    'view_name' => 'error',
                    'title' => Lang::get('page')['error'],
                    'data' => (array) $this->error
                ];
                return $page;
            }

            $doc = Models::load($rawns, $title, $rev);
            $page['title'] .= ' (r'.$rev.' RAW)';
            $page['data']['rev'] = $rev;
            $page['data']['text'] = $doc['content'];
            //'debug' => $this->uri_data
        }
        return $page;
    }
}