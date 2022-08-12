<?php
namespace PressDo;

require 'controllers/common.php';
require 'controllers/WikiACL.php';
require 'models/acl.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        list($rawns, $namespace, $title) = self::parse_title($this->uri_data->title);

        

        $doc = Models::load($rawns, $title, $this->uri_data->rev);

        
        $discussions = Models::get_doc_thread($rawns,$title);
        $docid = Models::get_doc_id($rawns, $title);

        $ACL = new WikiACL($rawns, $title, 'acl', $this->session, $this->error);
        $ACL->check();

        $doc_editable = $this->error?->code == 'permission_acl' ? false : true;
        $ns_editable = WikiACL::check_perms('nsacl', $this->session, $title) === true ? true:false;
        
        $acl_doc = Models::fetch_doc_acl(Models::get_doc_id($rawns, $title));
        $acl_ns = Models::fetch_ns_acl($rawns);

        $doc_acl = $ns_acl = ['read' => [], 'edit' => [], 'move' => [], 'delete' => [], 'create_thread' => [], 'write_thread_comment' => [], 'edit_request' => [], 'acl' => []];
        
        foreach($acl_doc as $acl){
            $acc = $acl['access'];
            unset($acl['access']);
            array_push($doc_acl[$acc], $acl);
        }
        foreach($acl_ns as $acl){
            $acc = $acl['access'];
            unset($acl['access']);
            array_push($ns_acl[$acc], $acl);
        }

        $page = [
            'view_name' => 'acl',
            'title' => $this->uri_data->title.' ('.Lang::get('page')['acl'].')',
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    //'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'docACL' => [
                    'acls' => $doc_acl,
                    'editable' => $doc_editable
                ],
                'nsACL' => [
                    'acls' => $ns_acl,
                    'editable' => $ns_editable
                ],
                'ACLTypes' => ['read', 'edit', 'move', 'delete', 'create_thread', 'write_thread_comment', 'edit_request', 'acl']
            ],
            'menus' => [],
            'debug' => Models::get_doc_id($rawns, $title),
            'customData' => []
        ];
        return $page;
    }
}