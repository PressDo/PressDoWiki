<?php
function loadMarkUp($content, $uri, array $options){
    require 'wikitext.php';
    WikitextParser::init();
    $parser = new WikitextParser($content);
    return ['html' => $parser->result, 'categories' => []];
}
