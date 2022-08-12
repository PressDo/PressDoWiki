<?php
function loadMarkUp($content, array $options){
    require 'wikitext.php';
    WikitextParser::init();
    $parser = new WikitextParser($content);
    return ['html' => $parser->result, 'categories' => []];
}
