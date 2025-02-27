<?php
namespace PressDo\app\Core;

use PressDo\app\Helpers\DefaultConfig;

require '../app/Helpers/DefaultConfig.php';
require '../app/Helpers/Database.php';
require '../app/Helpers/Config.php';
require '../app/Helpers/Languages.php';
require '../app/Helpers/Namespaces/'.DefaultConfig::get('wiki.language').'.php';
require '../app/Helpers/Router.php';
require '../app/Helpers/GeoIP.php';