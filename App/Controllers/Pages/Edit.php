<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{Document,Member};
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;
use PressDo\App\Helpers\{Languages,Namespaces};

class Edit extends Controller
{
    public string $content;

    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $error = [];
        $uuid = Document::getUuid($namespace, $title);
        $formReceived = isset($_POST['token']) && isset($_POST['content']);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');

        if ($error['code'] == 'permission_read'){
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
        }
        $ACL->check('edit');

        // 편집권한이 없으면 편집 요청 권한 확인
        if ($error['code'] == 'permission_edit') {
            $error1 = $error;
            $ACL->check('edit_request');
            if ($error['code'] !== 'permission_edit_request') {
                Header('Location: /new_edit_request/'.$this->uri_data->title);
                exit;
            } else
                $this->error = $error1; // overwrite with edit-error
        }

        if ($formReceived && ($namespace == Namespaces::file() || $namespace == Namespaces::user()))
            $this->error = self::makeErrorBox('invalid_namespace');

        if ($formReceived && !self::validateCaptcha($_POST[$this->api_config['captcha_token_name']]))
            $this->error = self::makeErrorBox('captcha_failed');

        // Edit Submission
        if ($formReceived && self::editFormProcess($this, 'edittoken') && empty($this->error)) {
            // Approve Edit
            $member = $this->session['member'] ? $this->session['uuid'] : null;
            $ip = !$member ? ($this->session['uuid'] ?? Member::getIpUuid($this->session['ip'])) : null;
            if (!$member && !$this->session['uuid']) {
                $this->session['uuid'] = $ip;
            }

            if ($this->session['baserev'] === 0)
                $action = 'create';
            
            if (!$uuid)
                $uuid = Document::create($namespace, $title);
            else {
                $this->session['baserev'] = Document::getVersion($uuid);
                if ($action == 'create')
                    Document::recreate($uuid);
            }
            
            Document::save(
                $uuid,
                $this->content,
                $_POST['comment'],
                $member,
                $ip,
                $this->session['baserev'],
                iconv_strlen($this->session['raw']),
                $action ?? 'modify'
            );
            

            Header('Location: /w/'.$this->uri_data->title);
            unset($this->session['edittoken'], $this->session['baserev'], $this->session['raw']);
            $_SESSION = $this->session;
            exit;
        }

        $doc = Document::load($uuid);

        $this->session['baserev'] = $doc['status'] !== 'delete' && $uuid ? Document::getVersion($uuid) : 0;
        $this->session['raw'] = $uuid ? $doc['content'] : '';
        $section = $_GET['section'];

        $page = [
            'view_name' => 'edit',
            'title' => $this->uri_data->title,
            'data' => [
                'body' => [
                    'baserev' => $this->session['baserev'],
                    'section' => $section,
                    'raw' => $_POST['content'] ?? $this->session['raw']
                ],
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                ],
                'user' => $namespace == Namespaces::user(),
                'token' => self::rand(64)
            //   'customData' => $ad_set
            ]
        ];

        $this->session['edittoken'] = $page['data']['token'];
        return $page;
    }
}