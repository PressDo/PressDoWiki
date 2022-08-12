<?php
function loadMarkUp($content, array $options){
    require 'Parsedown.php';
    $Parsedown = new Parsedown();
    
    return ['html' => $Parsedown->text($content), 'categories' => []];
}
