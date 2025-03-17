<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{History,Document};
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;

class MarkTroll extends Controller
{
    public function makeData(): void
    {
        if (!$this->session['member']) {
            $this->return();
        }

        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $error = [];
        $uuid = Document::getUuid($namespace, $title);
        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');
        $ACL->check('edit');

        if (empty($error)) {
            History::markTroll($_GET['uuid'], $this->session['uuid']);
        }

        $this->return();
    }

    private function return(): never
    {
        Header('Location: /history/'.$this->uri_data->title);
        exit;
    }
}