<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,Member,ACL as ACLModels};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\{Languages,GeoIP};

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

        $doc_acl = ['edit' => [], 'move' => [], 'delete' => [], 'create_thread' => [], 'write_thread_comment' => [], 'edit_request' => [], 'acl' => []];
        $ns_acl = ['read' => [], 'edit' => [], 'move' => [], 'delete' => [], 'create_thread' => [], 'write_thread_comment' => [], 'edit_request' => [], 'acl' => []];

        $aclgroups = array_values(ACLModels::aclgroups());

        if (!empty($_POST['acl_target'])) {
            // insert acl
            $acl_target = explode('.', $_POST['acl_target']);
            $acl_change = !empty($_POST['target_name']) && is_numeric($_POST['duration_raw']) && $_POST['duration_raw'] >= 0;
            $errormsg = '';

            if ($_POST['target_type'] == 'perm' && !in_array($_POST['target_name'], ['any', 'member', 'admin', 'member_signup_15days_ago', 'document_contributor', 'contributor', 'match_username_and_document_title']))
                $errormsg = 'invalid_acl_condition';

            if ($_POST['target_type'] == 'member' && !($member = Member::exist($_POST['target_name'])))
                $errormsg = 'invalid_aclgroup';

            if ($_POST['target_type'] == 'ip' && inet_pton($_POST['target_name']) === false)
                $errormsg = 'invalid_acl_condition';
            
            if ($_POST['target_type'] == 'geoip' && !in_array($_POST['target_name'], GeoIP::$geoCodes))
                $errormsg = 'invalid_aclgroup';

            if ($_POST['target_type'] == 'aclgroup' && !in_array($_POST['target_name'], $aclgroups))
                $errormsg = 'invalid_aclgroup';
                
            if ($acl_change && ${$acl_target[0].'_editable'} && in_array($acl_target[1], array_keys(${$acl_target[0].'_acl'}))
                && empty($errormsg) && in_array($_POST['action'], ['allow', 'deny', 'gotons'])) {
                
                if ($member)
                    $_POST['target_name'] = $member['uuid'];

                $expiry = $_POST['duration_raw'] > 0 ? time() + $_POST['duration_raw'] : 0;

                $tar_arg =  ($acl_target[0] == 'doc') ? $uuid : $namespace;

                if (ACLModels::isDuplicate($acl_target[0], $tar_arg, $acl_target[1], $_POST['target_type'].':'.$_POST['target_name']))
                    $errormsg = 'acl_already_exists';
                elseif ($acl_target[0] == 'doc') {
                    if (!$uuid) {
                        $uuid = Document::create($namespace, $title);
                        $baserev = 0;
                    } else
                        $baserev = Document::getVersion($uuid);
    
                    $aclchanged = ['insert', $acl_target[1], $_POST['action'], $_POST['target_type'].':'.$_POST['target_name']];
                    
                    $acldataobj = ['access' => $acl_target[1], 'condition' => $_POST['target_type'].':'.$_POST['target_name'], 'action' => $_POST['action'], 'until' => $expiry];
                    $editdataobj = ['baserev' => $baserev, 'contributor_m' => $this->session['member']['uuid'] ?? null, 'contributor_i' => $this->session['ip'], 'acl_changed' => implode(',', $aclchanged)];
    
                    ACLModels::addDocACL($uuid, $acldataobj, $editdataobj);
                } elseif ($acl_target[0] == 'ns') {
                    $acldataobj = ['access' => $acl_target[1], 'condition' => $_POST['target_type'].':'.$_POST['target_name'], 'action' => $_POST['action'], 'until' => $expiry];
                    ACLModels::addNSACL($namespace, $acldataobj);
                }
            }
        } elseif (!empty($_POST['target'])) {
            // remove acl
            $acl_target = explode('.', $_POST['target']);

            if (${$acl_target[0].'_editable'}) {
                if ($acl_target[0] == 'doc') {
                    if (!$uuid) {
                        $uuid = Document::create($namespace, $title);
                        $baserev = 0;
                    } else
                        $baserev = Document::getVersion($uuid);
                    
                    $rule = ACLModels::getDocRule($acl_target[1]);
                    
                    $aclchanged = ['delete', $rule['access'], $rule['action'], $rule['condition']];
                    $editdataobj = ['uuid' => $uuid, 'baserev' => $baserev, 'contributor_m' => $this->session['member']['uuid'] ?? null, 'contributor_i' => $this->session['ip'], 'acl_changed' => implode(',', $aclchanged)];
                    ACLModels::deleteDocACL($acl_target[1], $editdataobj);
                } elseif ($acl_target[0] == 'ns')
                    ACLModels::deleteNSACL($acl_target[1]);
            }
        }

        $acl_doc = $uuid ? ACLModels::fetchDocACL($uuid) : [];
        $acl_ns = ACLModels::fetchNSACL($namespace);
        
        foreach ($acl_doc as $acl) {
            $acc = $acl['access'];
            unset($acl['access']);
            array_push($doc_acl[$acc], $acl);
        }
        foreach ($acl_ns as $acl) {
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