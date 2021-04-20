<?php
/*
PressDo Wiki Syntax Processor
*/
function readSyntax($content, $noredirect = 0)
{
    global $conf;
    require_once('NamuMark.php');
    $NM = new NamuMark(array('read' => $content), $conf['허용사진확장자']);
    $content = $NM->parse(function($err, $result) {
    // result object can be diffrerent by renderer.
    if ($err)
        //console.log('ERROR!');
    $html = $result['html'];
    $categories = $result['categories'];
    //console.log('complete!');
});
    
    return $content;
}
?>
