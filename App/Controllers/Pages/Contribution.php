<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{History, Member};
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Namespaces,Languages,Config};

class Contribution extends Controller
{
    public function makeData(): array
    {
        $user = Member::lookup($this->uri_data->target);

        if (!$user)
            $user = Member::ipLookup($this->uri_data->target);

        if (!$user) {
            http_response_code(404);
            exit;
        }

        if (isset($_GET['from']))
            $from = intval($_GET['from']);

        if (isset($_GET['until']))
            $until = intval($_GET['until']);

        if ($this->uri_data->type == 'document') {
            $count = History::countContributionDocument($this->uri_data->target);
            $fetch = History::getContributionDocument($this->uri_data->target, $count, $from, $until);
        } elseif ($this->uri_data->type == 'discuss') {
            $fetch = History::getRecentContributionDiscuss($this->uri_data->target);
        } elseif ($this->uri_data->type == 'edit_request') {
            $fetch = History::getContributionEditrequest($this->uri_data->target);
            $count = History::countContributionEditrequest($this->uri_data->target);
        }

        $page = [
            'view_name' => '',
            'title' => '"'.$user.'" '.Languages::get('page', 'contribution'),
            'data' => [
                'count' => $count,
                'from' => $until && $until - 1 >= 1 ? $until - 1 : ($from && $from - 100 >= 0 ? $from - 100 : ($count > 100 ? $count - 100 : null)),
                'until' => $from && $from + 1 <= $count ? $from + 1 : ($until && $until + 100 <= $count ? $until + 100 : null),
                'content' => []
            ]
        ];

        $resultSet = [];
        if ($this->uri_data->type == 'document') {
            foreach($fetch as $f){
                $rs = [
                    'uuid' => History::bin2uuid($f['uuid']),
                    'document' => ['namespace' => $f['namespace'], 'title' => $f['title'], 'forceShowNamespace' => self::forceShowNamespace($f['namespace'], $f['title'])],
                    'date' => $f['datetime'],
                    'log' => $f['comment'],
                    'rev' => $f['rev'],
                    'count' => $f['count'],
                    'logtype' => $f['action'],
                    'target_rev' => $f['reverted_version'],
                    'acl' => $f['acl_changed'],
                    'from' => $f['moved_from'],
                    'to' => $f['moved_to'],
                    'user_mode' => []
                ];
                array_push($resultSet, $rs);
            }
        } elseif ($this->uri_data->type == 'discuss') {
            foreach ($fetch as $f){
                $rs = [
                    'slug' => $f['urlstr'],
                    'document' => ['namespace' => $f['namespace'], 'title' => $f['title'], 'forceShowNamespace' => self::forceShowNamespace($f['namespace'], $f['title'])],
                    'topic' => $f['topic'],
                    'no' => $f['no'],
                    'date' => $f['datetime'],
                    'user_mode' => []
                ];
                array_push($resultSet, $rs);
            }
        }
        $page['data']['content'] = $resultSet;
        
        return $page;
    }
}