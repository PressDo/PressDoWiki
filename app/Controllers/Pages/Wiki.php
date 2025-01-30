<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Backlink,Document,Star};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL;
use PressDo\app\Helpers\{Namespaces,Languages,Database};

class Wiki extends Controller
{
    public function updateLinktable(string $uuid, array $links): void
    {
        if (count($links['link']) > 0 || count($links['redirect']) > 0 || count($links['include']) > 0 || count($links['file']) > 0)
            Backlink::update($uuid, $links);
    }

    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);
        $error = [];

        $ACL = new ACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');

        if (isset($error['code']) && $error['code'] == 'permission_read')
            return [
                'view_name' => 'error',
                'title' => Languages::get('page')['error'],
                'data' => $error
            ];

        if (!$uuid) {
            # If Not Found
            $page = [
                'view_name' => 'notfound',
                'title' => $this->uri_data->title,
                'data' => [
                    'discuss_progress' => false,
                    'user' => $namespace == Namespaces::USER
                ]
            ];

            $error['code'] = 'notfound';
            $error['message'] = Languages::get('msg', 'document_not_found');
        } else {
            # If Found
            $discussions = Document::getDocThread($uuid);
            $rev_uuid = $_GET['uuid'] ?? null;

            
            $doc = Document::load($uuid, $rev_uuid);
            $content = self::readSyntax($doc['content'], [
                'title' => $this->uri_data->title,
                'noredirect' => $_GET['noredirect'],
                'db' => Database::getInstance(),
                'thread' => false
            ]);

            if (!$backlinkrefreshed)
                self::updateLinktable($uuid, $content['links']);

            if ($rev_uuid !== null)
                $page['subtitle'] = ($rev_uuid !== null)
                    ? str_replace('@1@', $doc['rev'], Languages::get('document', 'rev')) : '';

            $data = [
                'user' => $namespace == Namespaces::USER,
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'content' => htmlspecialchars($content['html']),
                    'categories' => $content['categories']
                ],
                'starred' => $this->session['member'] ? Star::ifStarred($uuid,$this->session['member']['uuid']) : false,
                'star_count' => Star::count($uuid),
                'discuss_progress' => isset($discussions[0]),
                'date' => $doc['datetime'],
                'rev' => $doc['rev']
            ];
            
            if ($namespace == Namespaces::USER) {
                $data['user'] = true;
                $data['userData'] = [
                    'admin' => in_array('admin', $ACL->perms),
                    'block' => [
                        'blocked' => false,
                        'seq',
                        'dt_html',
                        'datetime',
                        'memo',
                        'until'
                    ]
                ];
            } else {
                $data['user'] = false;
                $data['userData'] = null;
            }
            $page = [
                'view_name' => 'wiki',
                'title' => $this->uri_data->title,
                'data' => $data
            ];
            //'debug' => $this->uri_data
        }
        return $page;
    }
}