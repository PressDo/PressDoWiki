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
            return $page;
        }

        if(Models::exist($rawns,$title)){
            $lver = Models::get_version($rawns,$title);
            if(!$_GET['rev'])
                $rev = $lver;
            else
                $rev = $_GET['rev'];

            if(!is_numeric($rev) || $rev > $lver){
                $this->error->code = 'no_such_revision';
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