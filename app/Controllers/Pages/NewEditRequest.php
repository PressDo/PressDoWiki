<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,Member,EditRequest};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\{Languages,Namespaces};
use HyungJu\ReadableURL;

class NewEditRequest extends Controller
{
    public string $content;

    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $error = [];
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');

        if ($error['code'] !== 'permission_read')
            $ACL->check('edit_request');
        
        if ($error['code'] == 'permission_edit_request')
            $this->error = $error;
        
        if (!$uuid)
            $error = ['code' => 'no_such_document'];
        else
            $doc = Document::load($uuid);

        if ($doc['content'] === null)
            $error = ['code' => 'no_such_document'];
        
        if ($error['code'] == 'no_such_document' || $error['code'] == 'permission_read') {
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;
        }

        // Edit Submission
        if (isset($_POST['token']) && isset($_POST['content']) && self::editFormProcess($this, 'ertoken')) {
            // Approve Edit
            $member = $this->session['member'] ? $this->session['uuid'] : null;
            $ip = !$member ? ($this->session['uuid'] ?? Member::getIpUuid($this->session['ip'])) : null;
            if (!$member && !$this->session['uuid']) {
                $this->session['uuid'] = $ip;
            }
            
            $slug = ReadableURL::gen(wordCount: 4);
            EditRequest::create(
                $slug,
                $uuid,
                $this->content,
                $_POST['comment'],
                $member,
                $ip,
                $doc['rev']
            );
            

            Header('Location: /edit_request/'.$slug);
            unset($this->session['ertoken']);
            $_SESSION = $this->session;
            exit;
        }

        $page = [
            'view_name' => 'edit_edit_request',
            'title' => $this->uri_data->title,
            'data' => [
                'body' => [
                    'baserev' => $doc['rev'],
                    'raw' => $_POST['content'] ?? $doc['content']
                ],
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                ],
                'user' => $namespace == Namespaces::USER,
                'token' => self::rand(64)
            //   'customData' => $ad_set
            ]
        ];

        $this->session['ertoken'] = $page['data']['token'];
        return $page;
    }
}