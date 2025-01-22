<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/unstar.php';

use PressDo\Models;

class WikiPage extends WikiCore
{
    public function make_data()
    {
        list($namespace, $title) = self::parse_title($this->uri_data->title);

        if(!$this->session->member){
            Header('Location: /member/login?redirect='.$this->server->REQUEST_URI);
            exit;
        }

        Models::unstar_document(Models::get_doc_uuid($namespace,$title), $this->session->member->uuid);

        Header('Location: /w/'.$this->uri_data->title);
    }
}