<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/member/star.php';

use PressDo\Models;

class WikiPage extends WikiCore
{
    public function make_data()
    {
        list($rawns, $namespace, $title) = self::parse_title($this->uri_data->title);

        if(!$this->session->member){
            Header('Location: /member/login?redirect='.$this->server->REQUEST_URI);
            exit;
        }

        Models::star_document(Models::get_doc_id($rawns,$title), $this->session->member->username);

        Header('Location: /w/'.$this->uri_data->title);
    }
}