<?php
namespace PressDo;
use PressDo\WikiPage;
use PressDo\Router;

require 'Router.php';

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
if(!session_id())
    session_start();

$uri = Router::handle_uri($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);

// initial
$Wiki = new WikiPage();
$Wiki->uri_data = $uri;
if(!empty($_SESSION))
    $Wiki->session = $_SESSION['session'];

$Wiki->post = (object) $_POST;
$Wiki->server = (object) $_SERVER;

// Controller
$Wiki->page = $Wiki->make_data();
$Wiki->make_page();

// call page
echo $Wiki->get_page();
$_SESSION['session'] = $Wiki->session;
