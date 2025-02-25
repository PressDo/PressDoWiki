<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Backlink,Document,Star,ACL as ACLModels,Member,Files,Search,History};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL;
use PressDo\app\Helpers\{Namespaces,Languages,DefaultConfig, Config};

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
                        'title' => $title,
                        'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                    ],
                    'discuss_progress' => false,
                    'user' => $namespace == Namespaces::USER
                ]
            ];
        }
        
        $discussions = Document::getDocThread($uuid);
        $rev_uuid = $_GET['uuid'] ?? null;

        
        $doc = Document::load($uuid, $rev_uuid);

        if ($doc['content'] === null) {
            # Deleted Document
            $page = [
                'view_name' => 'notfound',
                'title' => $this->uri_data->title,
                'data' => [
                    'document' => [
                        'namespace' => $namespace,
                        'title' => $title,
                        'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                    ],
                    'history' => [],
                    'discuss_progress' => false,
                    'user' => $namespace == Namespaces::USER
                ]
            ];
            
            $his = History::load($uuid, count: 3);
            foreach ($his as $f) {
                if (!empty($f['contributor_i'])) {
                    $Cuuid = Document::bin2uuid($f['contributor_i']);
                    $ip = Member::ipLookup($Cuuid);
                    $member = null;
    
                    if (empty($userStyleSet[$ip])):
                        $groups = array_map(fn($r) => $r[0]['groupid'], ACLModels::getUserAclgroups($ip));
                        $userStyleSet[$ip] = implode(' ', array_map(fn($r) => Config::get('aclgroup.'.$r.'.style', ' '), $groups));
                    endif;
                } elseif(!empty($f['contributor_m'])) {
                    $Cuuid = Document::bin2uuid($f['contributor_m']);
                    $member = Member::lookup($Cuuid);
                    $ip = null;
    
                    if (empty($userStyleSet[$Cuuid])):
                        $groups = array_map(fn($r) => $r[0]['groupid'], ACLModels::getUserAclgroups(uuid: $Cuuid));
                        $userStyleSet[$Cuuid] = implode(' ', array_map(fn($r) => Config::get('aclgroup.'.$r.'.style', ' '), $groups));
                    endif;
    
                    if (empty($mperms[$Cuuid])) {
                        $mperms[$Cuuid] = [];
                        ACLModels::getAccountPerms($Cuuid, $member, $mperms[$Cuuid]);
                    }
                }
    
                array_push($page['data']['history'], [
                    'rev' => $f['rev'],
                    'uuid' => Document::bin2uuid($f['uuid']),
                    'log' => $f['comment'],
                    'date' => $f['datetime'],
                    'count' => $f['count'],
                    'logtype' => $f['action'],
                    'target_rev' => $f['reverted_version'],
                    'author' => $member,
                    'ip' => $ip,
                    'contributor_uuid' => $Cuuid,
                    'style' => $userStyleSet[$Cuuid] ?? $userStyleSet[$ip],
                    'admin' => $ip ?? in_array('admin', $mperms[$Cuuid]),
                    'edit_request' => $f['edit_request_uri'],
                    'acl' => $f['acl_changed'],
                    'from' => $f['moved_from'],
                    'to' => $f['moved_to'],
                    'user_mode' => []
                ]);
            }
            return $page;
        }
        
        $content = self::readSyntax($doc['content'], [
            'title' => $this->uri_data->title,
            'thread' => false
        ]);

        // Refresh Backlinks and Search Index
        if (!$backlinkrefreshed) {
            self::updateSearchIndex($uuid, $content['html']);
            self::updateLinktable($uuid, $content['links']);
        }

        if (!empty($content['links']['redirect'])) {
            [$lns, $lt] = self::parseTitle($content['links']['redirect'][0]);

            // redirect only to valid link (document exists), without noredirect and from
            if (Document::getUuid($lns, $lt) !== false && $_GET['noredirect'] !== '1' && empty($_GET['from']))
                header('Location: /w/'.$content['links']['redirect'][0].'?from='.$this->uri_data->title);
        }
        
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
                                'forceShowNamespace' => self::forceShowNamespace($b[0]['namespace'], $t)
                            ],
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
                'forceShowNamespace' => self::forceShowNamespace($namespace, $title),
                'content' => htmlspecialchars($content['html']),
                'categories' => $content['links']['category']
            ],
            'category_documents' => $cat_documents,
            'starred' => $this->session['member'] ? Star::ifStarred($uuid,$this->session['uuid']) : false,
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

            ACLModels::getAccountPerms($mdata['uuid'], $title, $mperms);
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
        } elseif ($namespace == Namespaces::FILE) {
            $file = Files::load($uuid);
            $ext = str_replace(['jpg', 'png'], 'webp', implode('', array_slice(explode('.', $title), -1, 1)));
            $data['file_endpoint'] = '/'.substr($file['hash'], 0, 2).'/'.$file['hash'].'.'.$ext;
            $data['transparent_img'] = self::getTransparentBackground($file['width'], $file['height']);
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

    private function updateSearchIndex(string $uuid, string $t): void
    {
        if (DefaultConfig::get('wiki.search_engine') !== 'SQL')
            return;
        /*$t = preg_replace('/<style[^>]*>[^<]*<\/style>/', '', $t);
        $t = preg_replace('/<div class=\"wiki-macro-toc\"[^>]*>[^<]*<\/div>/', '', $t);
        $t = preg_replace('/<a id[^>]*href=\"#toc\">[^<]*<\/a><span id[^>]>([^<]*)<span[^>]><\/span>/', '$1', $t);
        $t = strip_tags($t);*/
        //Dom\HTMLDocument::createFromString();
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // HTML 파싱 오류 방지
        //$dom->loadHTML('<?xml encoding="UTF-8">'.$t);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$t);
        libxml_clear_errors();
        $xp = new \DOMXPath($dom);
        // style 태그
        //$rs = $dom->getElementsByTagName('style');
        //foreach ($rs as $r) {
        //    $r->remove();
        //}
        // 목차
        if ($dom->getElementById('toc'))
            $dom->getElementById('toc')->remove();

        // 각주
        /*$nds = $xp->query("//a[contains(@class, 'wiki-fn-content')]");
        foreach ($nds as $node) {
            $span = $node->getElementsByTagName('span')->item(0);
            if ($span && $span->hasAttribute('id')) {
                $originalId = $span->getAttribute('id');
                $newId = substr($originalId, 1);
                
                $existingSpan = $xp->query("//span[@id='$newId']")->item(0);
                
                if ($existingSpan) {
                    $parentNode = $existingSpan->parentNode;
                    $node->parentNode->replaceWith($node, $parentNode);
                }
            }
        }*/
        
        // 문단
        for ($i=1; $i<=6; $i++) {
            $rs = $dom->getElementsByTagName('h'.$i);
            foreach ($rs as $r) {
                $rtg = $r->getElementsByTagName('a')->item(0);
                $rtgt = $r->getElementsByTagName('span')->item(0);
                $r->removeChild($rtg);
            }
        }
        $t = $dom->saveHTML();
        $t = preg_replace('/<style[^>]*>[^<]*<\/style>/', '', $t);
        $t = strip_tags($t);
        $t = preg_replace('/{{{#!wiki style=\"[^"]*\"\n(.*)}}}/', ' $1 ', $t);

        $result = html_entity_decode($t);
        $result = preg_replace('/( {2,})/', ' ', $result);
        $result = str_replace("\n", ' ', $result);
        Search::updateIndex($uuid, $result);
    }
}