<?php
function loadMarkUp($content, $uri, array $options){
    require "Parser.php";
    $parser = new JBBCode\Parser();
    $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
    $parser->parse($content);
    return ['html' => $parser->getAsHtml(), 'categories' => []];
}
