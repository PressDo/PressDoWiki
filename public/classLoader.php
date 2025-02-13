<?php
namespace PressDo;

use PressDo\app\Helpers\Config;

function loadClass($page, $menu) {
    $DIR = '../app/Models/';
    $HDIR = '../app/Helpers/Mark/';

    require $DIR.'ACL.php';

    switch ($page) {
        case 'wiki':
            require $DIR.'Star.php';
            require $HDIR.Config::get('wiki.mark').'/Loader.php';
            require $DIR.'Member.php';
            // no break
        case 'backlink':
            require $DIR.'Document.php';
            require $DIR.'Backlink.php';
            break;
        case 'BlockHistory':
            require $DIR.'BlockHistory.php';
            // no break
        case 'RecentDiscuss':
            require $DIR.'Thread.php';
            require $DIR.'Member.php';
            // no break
        case 'edit':
        case 'move':
        case 'acl':
        case 'raw':
        case 'random':
        case 'RandomPage':
        case 'OldPages':
        case 'LongestPages':
        case 'ShortestPages':
        case 'NeededPages':
        case 'UncategorizedPages':
            require $DIR.'Document.php';
            break;
        case 'RecentChanges':
        case 'history':
            require $DIR.'Member.php';
            // no break
        case 'diff':
            require $DIR.'Document.php';
            require $DIR.'History.php';
        break;
        case 'discuss':
            require $DIR.'EditRequest.php';
            require $DIR.'Document.php';
        case 'thread':
            require $DIR.'Member.php';
            require $DIR.'Thread.php';
        case 'api':
            if ($menu == 'preview' || $page == 'thread') {
                require $HDIR.Config::get('wiki.mark').'/Loader.php';
                require $DIR.'Document.php';
            }
            break;
        case 'admin':
            if ($menu == 'grant' || $menu == 'login_history')
                require $DIR.'Member.php';
            break;
        case 'member':
            if ($menu == 'unstar' || $menu == 'star') {
                require $DIR.'Document.php';
                require $DIR.'Star.php';
            }
            // no break 
        case 'License':
        case 'aclgroup':
            require $DIR.'Member.php';
            break;
    }
}