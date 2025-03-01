<?php
namespace PressDo\App\Controllers\Pages\Member;

use PressDo\App\Models\Member;
use PressDo\App\Core\Controller;

class Logout extends Controller
{
    public function makeData(): void
    {
        if (isset($_COOKIE['szczecin'])) {
            Member::deleteCookie($this->session['uuid'], 'szczecin', $_COOKIE['szczecin']);
            setcookie('szczecin', '', self::getCookieOptions(-3600));
        }
        unset($this->session);
        session_destroy();
        if(!empty($_GET['redirect']))
            Header('Location: '.$_GET['redirect']);
        else
            Header('Location: /');
        exit;
    }
}