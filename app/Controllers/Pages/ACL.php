<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,ACL as ACLModels};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\Languages;

class ACL extends Controller
{
    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);
        $error = [];
        //$doc = Models::load($uuid, $this->uri_data->rev);
        //$discussions = Models::get_doc_thread($uuid);

        $ACL = new WikiACL($namespace, $title, 'acl', $this->session, $error);
        $ACL->check('acl');

        $doc_editable = !($error['code'] == 'permission_acl');
        $ns_editable = in_array('nsacl', $ACL->perms);
        
        $acl_doc = ACLModels::fetchDocACL($uuid);
        $acl_ns = ACLModels::fetchNSACL($namespace);

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
            'title' => $this->uri_data->title,
            'subtitle' => Languages::get('page', 'acl'),
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
            //'debug' => Models::get_doc_uuid($namespace, $title),
            'customData' => []
        ];
        return $page;
    }
}