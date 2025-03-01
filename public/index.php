<?php
namespace PressDo;

require '../vendor/autoload.php';

use PressDo\App\Helpers\{Config,Router,GeoIp};
use PressDo\App\Core\Controller;

date_default_timezone_set(GeoIp::getTimezone(Controller::getIpAddr()) ?? Config::get('wiki.timezone'));
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if(!session_id())
    session_start();

$router = new Router();
$router->handleURI($_SERVER['REQUEST_URI']);

switch ($router->uri_data->page) {
    case 'member':
        // no break
    case 'admin':
        // no break
    case 'api':
        $fnClassNm = $router->uri_data->page.'\\'.str_replace('_', '', ucwords($router->uri_data->menu, '_'));
        break;
    case 'acl':
        $fnClassNm = 'ACL';
        break;
    default:
        $fnClassNm = str_replace('_', '',ucwords($router->uri_data->page, '_'));
}

// initial
$pageClassName = 'PressDo\App\Controllers\Pages\\'.$fnClassNm;
$wiki = new $pageClassName();
$wiki->uri_data = $router->uri_data;

header("Content-Security-Policy: default-src 'self'; img-src 'self' *.theseed.io secure.gravatar.com www.google-analytics.com 
http://tn-skr2.smilevideo.jp data:; media-src *; child-src *; script-src 'self' 'unsafe-eval' 'unsafe-inline' www.google.com www.gstatic.com 
www.googletagmanager.com www.google-analytics.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; connect-src 'self'; font-src 'self' 
fonts.gstatic.com data:;");

// Controller
$wiki->page = $wiki->makeData();

// call page
$getPage = $router->uri_data->page !== 'api';
$wiki->makePage($getPage);

$_SESSION = $wiki->session;