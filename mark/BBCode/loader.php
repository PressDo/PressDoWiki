<?php
function loadMarkUp($content, array $options){
    require_once "Parser.php";
    $parser = new JBBCode\Parser();
    $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
    $parser->parse($content);
    return ['html' => $parser->getAsHtml(), 'categories' => []];
}
