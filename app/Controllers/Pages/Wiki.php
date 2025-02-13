<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Backlink,Document,Star,ACL as ACLModels,Member};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL;
use PressDo\app\Helpers\{Namespaces,Languages,Database,Config};

class Wiki extends Controller
{
    public function updateLinktable(string $uuid, array $links): void
    {
        if (!empty($links['link']) || !empty($links['redirect']) || !empty($links['include']) || !empty($links['file']) || !empty($links['category']))
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
        
        
        $ACL->check('edit');
        if (isset($error['code']) && $error['code'] == 'permission_edit')
            $editable = false;
        else
            $editable = true;

        if (!$uuid) {
            # If Not Found
            return [
                'view_name' => 'notfound',
                'title' => $this->uri_data->title,
                'data' => [
                    'document' => [
                        'namespace' => $namespace,
                        'title' => $title
                    ],
                    'discuss_progress' => false,
                    'user' => $namespace == Namespaces::USER
                ]
            ];
        }
        
        $discussions = Document::getDocThread($uuid);
        $rev_uuid = $_GET['uuid'] ?? null;

        
        $doc = Document::load($uuid, $rev_uuid);
        $content = self::readSyntax($doc['content'], [
            'title' => $this->uri_data->title,
            'db' => Database::getInstance(),
            'thread' => false
        ]);

        if (!$backlinkrefreshed)
            self::updateLinktable($uuid, $content['links']);

        if (!empty($content['links']['redirect'])) {
            [$lns, $lt] = self::parseTitle($content['links']['redirect'][0]);

            // redirect only to valid link (document exists), without noredirect and from
            if (Document::getUuid($lns, $lt) !== false && $_GET['noredirect'] !== '1' && empty($_GET['from']))
                header('Location: /w/'.$content['links']['redirect'][0].'?from='.$this->uri_data->title);
        }

        if ($rev_uuid !== null)
            $page['subtitle'] = ($rev_uuid !== null)
                ? str_replace('@1@', $doc['rev'], Languages::get('document', 'rev')) : '';
        
        $cat_documents = [];
        if ($namespace == Namespaces::CATEGORY) {
            foreach (Namespaces::all() as $n) {
                $bl = Backlink::get($namespace, $title, $n);

                if (!empty($bl)) {
                    ksort($bl);
                    foreach ($bl as $t => $b) {
                        // backlink 정렬
                        $firstchar = iconv_substr($t, 0, 1);
                        $head = self::is_hangeul($firstchar) ? self::ko_head($firstchar) : $firstchar;

                        if (!isset($cat_documents[$n][$head]))
                            $cat_documents[$n][$head] = [];

                        if (!isset($cat_documents[$n]['count']))
                            $cat_documents[$n]['count'] = strval($b[0]['total_count']);
                        
                        array_push($cat_documents[$n][$head], [
                            'document' => [
                                'namespace' => $b[0]['namespace'], 
                                'title' => $t, 
                                'force_show_namespace' => Config::get('wiki.force_show_namespace')], 
                                'type' => $b[0]['type']
                        ]);
                    }
                }
            }
        }

        $data = [
            'user' => $namespace == Namespaces::USER,
            'document' => [
                'namespace' => $namespace,
                'title' => $title,
                'content' => htmlspecialchars($content['html']),
                'categories' => $content['links']['category']
            ],
            'category_documents' => $cat_documents,
            'starred' => $this->session['member'] ? Star::ifStarred($uuid,$this->session['member']['uuid']) : false,
            'star_count' => Star::count($uuid),
            'discuss_progress' => isset($discussions[0]),
            'date' => $doc['datetime'],
            //'rev' => $doc['rev'],
            'editable' => $editable
        ];
        
        if ($namespace == Namespaces::USER) {
            $mperms = [];
            $block = ['blocked' => false];
            $mdata = Member::exist($title);

            ACLModels::getAccountPerms(['username' => $title, 'uuid' => $mdata['uuid']], $mperms);
            $groups = ACLModels::getUserAclgroups(uuid: $mdata['uuid']);

            foreach ($groups as $g) {
                if ($g[0]['groupid'] == '1') {
                    $block = [
                        'blocked' => true,
                        'seq' => $g[0]['id'],
                        'datetime' => $g[0]['datetime'],
                        'memo' => $g[0]['comment'],
                        'until' => $g[0]['until']
                    ];
                }
            }

            $data['user'] = true;
            $data['userData'] = [
                'admin' => in_array('admin', $mperms),
                'block' => $block
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
        //var_dump($page);
        //'debug' => $this->uri_data
        return $page;
    }
}