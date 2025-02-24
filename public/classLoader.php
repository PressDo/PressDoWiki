<?php
namespace PressDo;

use PressDo\app\Helpers\Config;

function loadClass($page, $menu) {
    $DIR = '../app/Models/';
    $HDIR = '../app/Helpers/Mark/';

    require $DIR.'ACL.php';

    switch ($page) {
        case 'wiki':
            require $DIR.'Files.php';
            require $DIR.'Star.php';
            require $DIR.'Search.php';
            require $HDIR.Config::get('wiki.mark').'/Loader.php';
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
        case 'diff':
            require $DIR.'Document.php';
        case 'contribution':
            require $DIR.'History.php';
            break;
        case 'discuss':
            require $DIR.'EditRequest.php';
            // no break
        case 'thread':
            require $DIR.'Thread.php';
            // no break
        case 'api':
            require $DIR.'Document.php';
            if ($menu == 'preview' || $page == 'thread') {
                require $HDIR.Config::get('wiki.mark').'/Loader.php';
            } elseif ($menu == 'recent') {
                require $DIR.'History.php';
            } elseif ($menu == 'search') {
                require $DIR.'Search.php';
            }
            break;
        case 'member':
            if ($menu == 'unstar' || $menu == 'star') {
                require $DIR.'Document.php';
                require $DIR.'Star.php';
            }
            break;
        case 'Upload':
            require $DIR.'Document.php';
            require $DIR.'Files.php';
            require '../app/Helpers/Uploaders/'.Config::get('storage.type').'.php';
            break;
        case 'Search':
            require $DIR.'Search.php';
    }
}