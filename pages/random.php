<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';

$r = Data::goRandom();
Header('Location: http://'.$conf['Domain'].$conf['ViewerUri'].$r['DocNm']);