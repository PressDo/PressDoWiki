<?php
function loadMarkUp($content, array $options){
    require_once 'Parsedown.php';
    $Parsedown = new Parsedown();
    
    return ['html' => $Parsedown->text($content), 'categories' => []];
}
