<?php
use PressDo\PressDo;
require 'PressDoLib.php';
require 'data/global/config.php';

$ref = $_SERVER['HTTP_REFERER'];
$urlstr = $conf['FullURL'];

if($conf['UseShortURI'] === true){
    $e = explode('/', $ref);
    $tstr = implode('', array_slice($e, 2));
}else{
    $e = parse_url($ref, PHP_URL_QUERY);
    parse_str($e, $f);
    $tstr = $f['title'];
}

if($_GET['isSearchBar'] == '1'){
    if(PressDo::exist($_GET['q']))
        $urlstr .= $uri['wiki'].$_GET['q'];
    else
        $urlstr .= $uri['Search'].$_GET['q'];
}else{
    switch($_GET['t']){
        case 'FrontPage':
            $urlstr .= $uri['wiki'].$conf['FrontPage'];
            break;
        case 'RecentChanges':
            $urlstr .= $uri['RecentChanges'];
            break;
        case 'RecentDiscuss':
            $urlstr .= $uri['RecentDiscuss'];
            break;
        case 'random':
            $urlstr .= $uri['random'];
            break;
        case 'edit':
            $urlstr .= $uri['edit'].$tstr;
            break;
    }
}
Header('Location: '.$urlstr);