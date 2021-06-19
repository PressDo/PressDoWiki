<?php
function loadMarkUp($title, $content){
    require "Parser.php";
    $parser = new JBBCode\Parser();
    $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
    $parser->parse($content);
    return ['html' => $parser->getAsHtml(), 'categories' => []];
}
