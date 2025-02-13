<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Document;
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\{Languages,Config};

class Edit extends Controller
{
    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $error = [];
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');

        if ($error['code'] == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;//$this::make_error();
        }
        $ACL->check('edit');

        // 편집권한이 없으면 편집 요청 권한 확인
        if ($error['code'] == 'permission_edit') {
            $error1 = $error;
            $ACL->check('edit_request');
            if ($error['code'] !== 'permission_edit_request')
                Header('Location: /new_edit_request/'.$this->uri_data->title);
            else
                $this->error = $error1; // overwrite with edit-error
        }

        // Edit Submission
        if (isset($_POST['token']) && isset($_POST['content'])) {
            $_POST['content'] = htmlspecialchars_decode($_POST['content']);

            $content = preg_replace('/^#(redirect|넘겨주기) (.+)$/im', '#redirect $2', $_POST['content']);

            // ignore string after redirect
            if (preg_match('/^#redirect (.+)$/im', $content, $matches)) {
                $content = $matches[0];
            }

            if ($_POST['token'] !== $this->session['token']) {
                // Reject: wrong anti-CSRF token
                $error = [
                    'code' => 'err_csrf_token',
                    'message' => Languages::get('msg', 'err_csrf_token'),
                    'errbox' => true
                ];
                $this->error = $error;
            } elseif ($this->session['raw'] == $content) {
                // Reject: same doc content
                $error = [
                    'code' => 'err_same_contents',
                    'message' => Languages::get('msg', 'err_same_contents'),
                    'errbox' => true
                ];
                $this->error = $error;
            } else {
                // Approve Edit
                $member = @$this->session['member']['uuid'];
                $ip = !$member ? $this->session['ip'] : null;

                if (!$uuid) {
                    $uuid = Document::create($namespace, $title);
                    $action = 'create';
                }
                
                Document::save(
                    $uuid,
                    $content,
                    $_POST['comment'],
                    $member,
                    $ip,
                    $this->session['baserev'],
                    iconv_strlen($this->session['raw']),
                    $action ?? 'modify'
                );
                

                Header('Location: /w/'.$this->uri_data->title);
                unset($this->session['token']);
            }
        }

        $doc = Document::load($uuid);

        $this->session['baserev'] = $uuid ? Document::getVersion($uuid) : 0;
        $this->session['raw'] = $uuid ? $doc['content'] : '';
        $section = $_GET['section'];

        $page = [
            'view_name' => 'edit',
            'title' => $this->uri_data->title,
            'data' => [
                'body' => [
                    'baserev' => $this->session['baserev'],
                    'section' => $section,
                    'raw' => $this->session['raw']
                ],
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'ForceShowNameSpace' => Config::get('wiki.force_show_namespace')
                ],
                'user' => ($namespace == '사용자'),
                'token' => self::rand(64)
            //   'customData' => $ad_set
            ]
        ];

        $this->session['token'] = $page['data']['token'];
        return $page;
    }
}