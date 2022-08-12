<?php
namespace PressDo;

require 'controllers/common.php';

class WikiPage extends WikiCore
{
    public function make_data()
    {
        $ref = $this->uri_data->query->redirect;
        unset($this->session);
        session_destroy();
        if(!empty($ref))
            Header('Location: '.base64_decode($ref));
        else
            Header('Location: /');
    }
}