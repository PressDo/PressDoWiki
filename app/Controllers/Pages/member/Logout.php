<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;

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