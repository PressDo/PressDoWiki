<?php
if(!function_exists('loadMarkUp')){
function loadMarkUp($content, array $options){
    require 'NamuMark.php';
    // MySQLWikiPage와는 달리 PlainWikiPage의 첫 번째 인수로 위키텍스트를 받습니다.
    $wPage = new PlainWikiPage($content);
    $wEngine = new NamuMark($wPage);
    $wEngine->noredirect = $options['noredirect'];
    $wEngine->prefix = "";
    $wEngine->title = $options['title'];
    $wEngine->inThread = $options['thread'];
    $wEngine->db = $options['db'];
    $wEngine->ns = $options['namespace'];

    // toHtml을 호출하면 HTML 페이지가 생성됩니다.
    return ['html' => $wEngine->toHtml(), 'categories' => $wEngine->category];
}
}
