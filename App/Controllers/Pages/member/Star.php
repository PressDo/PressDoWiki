<?php
namespace PressDo\App\Controllers\Pages\Member;

use PressDo\App\Models\{Document,Star as S};
use PressDo\App\Core\Controller;

class Star extends Controller
{
    public function makeData(): void
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);

        if(!$this->session['member']){
            Header('Location: /member/login?redirect='.$_SERVER['REQUEST_URI']);
            exit;
        }

        S::star(Document::getUuid($namespace,$title), $this->session['uuid']);

        Header('Location: /w/'.$this->uri_data->title);
    }
}