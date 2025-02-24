<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Core\Controller;

class Logout extends Controller
{
    public function makeData(): void
    {
        unset($this->session);
        session_destroy();
        if(!empty($_GET['redirect']))
            Header('Location: '.$_GET['redirect']);
        else
            Header('Location: /');
        exit;
    }
}