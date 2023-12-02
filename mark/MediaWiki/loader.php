<?php
function loadMarkUp($content, array $options){
    require_once 'wikitext.php';
    WikitextParser::init();
    $parser = new WikitextParser($content);
    return ['html' => $parser->result, 'categories' => []];
}
