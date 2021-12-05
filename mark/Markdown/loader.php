<?php
function loadMarkUp($content, $uri, array $options){
    require 'Parsedown.php';
    $Parsedown = new Parsedown();
    
    return ['html' => $Parsedown->text($content), 'categories' => []];
}
