<?php
function loadMarkUp($title, $content){
    require 'Parsedown.php';
    $Parsedown = new Parsedown();
    
    return ['html' => $Parsedown->text($content), 'categories' => []];
}
