<?php
function loadMarkUp($title, $content){
    require 'wikitext.php';
    WikitextParser::init();
    $parser = new WikitextParser($content);
    return ['html' => $parser->result, 'categories' => []];
}
