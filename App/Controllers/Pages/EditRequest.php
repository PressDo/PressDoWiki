<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{Document,EditRequest as ER,Member,ACL};
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;
use PressDo\App\Helpers\{Languages,Namespaces,Config};

class EditRequest extends Controller
{
    public string $content;

    public function makeData(): array
    {
        $error = [];
        $erdata = ER::get($this->uri_data->title);

        $ACL = new WikiACL($erdata['namespace'], $erdata['title'], $erdata['document'], $this->session, $error);
        $ACL->check('read');

        if ($erdata === null)
            $error = ['code' => 'editrequest_not_found'];

        if (!empty($error['code'])) {
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
        }

        $title = (self::forceShowNamespace($erdata['namespace'], $erdata['title']) ? $erdata['namespace'].':' : '').$erdata['title'];
        $baserevdat = Document::load($erdata['document'], $erdata['baserev']);

        $contributor = self::getUserIdfData($erdata['contributor_m'], $erdata['contributor_i']);
        $executor = self::getUserIdfData($erdata['executor_m'], $erdata['executor_i']);

        $content = self::readSyntax($erdata['content'], [
            'title' => self::makeTitle($erdata['namespace'], $erdata['title']),
            'thread' => false
        ]);

        if (/*$error['code'] == 'no_such_document' || */$error['code'] == 'permission_read') {
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
        }

        if ($this->uri_data->action == 'edit' && $erdata['status'] == 'open') {
            if ($error['code'] !== 'permission_read')
                $ACL->check('edit_request');

            if ($error['code'] == 'permission_edit_request')
                $this->error = $error;

            if ($this->session['uuid'] !== $contributor['uuid']) {
                $error = ['code' => 'err_not_self_modify'];
                return [
                    'view_name' => 'error',
                    'title' => Languages::get('page', 'error'),
                    'data' => $error
                ];
            }

            // Edit Submission
            if (isset($_POST['token']) && isset($_POST['content']) && self::editFormProcess($this, 'eretoken')) {
                // Approve Edit
                ER::modify(
                    $this->uri_data->title,
                    $this->content,
                    $_POST['comment'],
                    iconv_strlen($this->content) - iconv_strlen($baserevdat['content'])
                );
                
                Header('Location: /edit_request/'.$this->uri_data->title);
                unset($this->session['ertoken']);
                $_SESSION = $this->session;
                exit;
            }

            $page = [
                'view_name' => 'edit_edit_request',
                'title' => $title,
                'data' => [
                    'body' => [
                        'baserev' => $erdata['baserev'],
                        'raw' => $_POST['content'] ?? $erdata['content'],
                        'comment' => $erdata['comment']
                    ],
                    'document' => [
                        'namespace' => $erdata['namespace'],
                        'title' => $erdata['title'],
                        'forceShowNamespace' => self::forceShowNamespace($erdata['namespace'], $erdata['title'])
                    ],
                    'user' => $erdata['namespace'] == Namespaces::user(),
                    'token' => self::rand(64)
                ]
            ];
    
            $this->session['eretoken'] = $page['data']['token'];
            return $page;
        } elseif ($this->uri_data->action == 'close' && $erdata['status'] == 'open' && ($this->session['uuid'] == $contributor['uuid'] || $error['code'] !== 'permission_edit')) {
            $member = $this->session['member'] ? $this->session['uuid'] : null;
            $ip = !$member ? ($this->session['uuid'] ?? Member::getIpUuid($this->session['ip'])) : null;
            if (!$member && !$this->session['uuid']) {
                $this->session['uuid'] = $ip;
            }
            ER::close(
                $this->uri_data->title,
                $_POST['reason'],
                $member,
                $ip,
                isset($_POST['lock'])
            );
            
            Header('Location: /edit_request/'.$this->uri_data->title);
            exit;
        } elseif ($this->uri_data->action == 'reopen' && $erdata['status'] !== 'open' && ($erdata['status'] !== 'locked' || in_array('admin', $ACL->perms))) {
            ER::reopen($this->uri_data->title);
            Header('Location: /edit_request/'.$this->uri_data->title);
            exit;
        } elseif ($this->uri_data->action == 'accept' && $erdata['status'] == 'open' && $error['code'] !== 'permission_edit') {
            $member = $this->session['member'] ? $this->session['uuid'] : null;
            $ip = !$member ? ($this->session['uuid'] ?? Member::getIpUuid($this->session['ip'])) : null;
            if (!$member && !$this->session['uuid']) {
                $this->session['uuid'] = $ip;
            }
            $rev = Document::getVersion($erdata['document']);
            ER::accept($this->uri_data->title, $erdata, $member, $ip, $rev + 1);
            Header('Location: /edit_request/'.$this->uri_data->title);
            exit;
        }

        if ($error['code'] !== 'permission_read')
            $ACL->check('edit');

        $page = [
            'view_name' => 'edit_request',
            'title' => $title,
            'data' => [
                'document' => [
                    'namespace' => $erdata['namespace'],
                    'title' => $erdata['title'],
                    'forceShowNamespace' => self::forceShowNamespace($erdata['namespace'], $erdata['title'])
                ],
                'body' => [
                    'slug' => $this->uri_data->title,
                    'status' => $erdata['status'],
                    'comment' => $erdata['comment'],
                    'baserev' => $erdata['baserev'],
                    'contributor_m' => $contributor['member'],
                    'contributor_i' => $contributor['ip'],
                    'contributor_uuid' => $contributor['uuid'],
                    'contributor_admin' => $contributor['admin'],
                    'contributor_style' => $contributor['style'],
                    'executor_m' => $executor['member'],
                    'executor_i' => $executor['ip'],
                    'executor_uuid' => $executor['uuid'],
                    'executor_admin' => $executor['admin'],
                    'executor_style' => $executor['style'],
                    'reason' => $erdata['reason'],
                    'lengthdiff' => $erdata['count'],
                    'createdtime' => $erdata['datetime'],
                    'lastedit' => $erdata['lastedit'],
                    'acceptrev' => $erdata['acceptrev']
                ],
                'can_accept' => false,
                'can_close' => true,
                'can_lock' => false,
                'can_reopen' => false,
                'can_edit' => false,
                'diff' => self::load_diff(
                    $baserevdat['content'],
                    $erdata['content'],
                    Languages::get('page', 'edit_request').' '.$this->uri_data->title
                ),
                'editor_comment' => $content['editor_comment'],
                'preview' => $content['html'],
                'user' => $erdata['namespace'] == Namespaces::user()
            //   'customData' => $ad_set
            ]
        ];

        if ($page['data']['body']['status'] == 'open') {
            if ($error !== 'permission_edit') {
                $page['data']['can_accept'] = true;
                $page['data']['can_close'] = true;
            }

            if (in_array('admin', $ACL->perms))
                $page['data']['can_lock'] = true;

            if ($this->session['uuid'] == $contributor['uuid']) {
                $page['data']['can_edit'] = true;
                $page['data']['can_close'] = true;
            }
        } elseif ($page['data']['body']['status'] == 'close' || $page['data']['body']['status'] == 'locked') {
            if ($page['data']['body']['status'] !== 'locked' || in_array('admin', $ACL->perms))
                $page['data']['can_reopen'] = true;
        }

        return $page;
    }

    private function getUserIdfData(?string $uuid, ?string $ip): array
    {
        $mperms = [];
        if (!empty($ip)) {
            $Cuuid = Document::bin2uuid($ip);
            $ip = Member::ipLookup($Cuuid);
            $member = null;
        } elseif(!empty($uuid)) {
            $Cuuid = Document::bin2uuid($uuid);
            $member = Member::lookup($Cuuid);
            $ip = null;

            if (empty($mperms[$Cuuid])) {
                $mperms[$Cuuid] = [];
                ACL::getAccountPerms($Cuuid, $member, $mperms[$Cuuid]);
            }
        }
        $groups = array_map(fn($r) => $r[0]['groupid'], ACL::getUserAclgroups($ip, $uuid));
        $style = implode(' ', array_map(fn($r) => Config::get('aclgroup.'.$r.'.style', ' '), $groups));
        return [
            'member' => $member,
            'ip' => $ip,
            'uuid' => $Cuuid,
            'admin' => $mperms[$Cuuid] === null ? false : in_array('admin', $mperms[$Cuuid]),
            'style' => $style
        ];
    }
}