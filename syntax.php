<?php
/*
PressDo Wiki Syntax Processor
*/
function readSyntax($content)
{
    // nowiki 이스케이프 정규식 (?<!{{{)
    // https://www.phpliveregex.com/#tab-preg-replace
    // 각주
    $content = preg_replace('/((?<!{{{)\[\* (.*?)\])/s', '<ref>$2</ref>', $content);

    // 볼드체
    $content = preg_replace('/(?<!{{{)\*\*(.*?)\*\*/s', '<b>$1</b>', $content);
    $content = preg_replace('/(?<!{{{)\'\'\'(.*?)\'\'\'/s', '<b>$1</b>', $content);

    // 이탤릭(반드시 볼드체보다 나중에)
    $content = preg_replace('/(?<!{{{)\*(.*?)\*/s', '<i>$1</i>', $content);
    $content = preg_replace('/(?<!{{{)\'\'(.*?)\'\'/s', '<i>$1</i>', $content);

    // 취소선
    $content = preg_replace('/(?<!{{{)~~(.*?)~~/s', '<del>$1</del>', $content);
    $content = preg_replace('/(?<!{{{)--(.*?)--/s', '<del>$1</del>', $content);

    // 글머리
    $content = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $content);

    // 주석
    $content = preg_replace('/^#(.*?)$/m', '', $content);
    $content = preg_replace('/^\/\/(.*?)$/m', '', $content);
    $content = preg_replace('/(?<!{{{)\/\*(.*?)\*\//s', '', $content);

    // 링크
    $content = preg_replace('/\[\[(.*?)\]\]/', '<a href="/index.php?title='.urlencode(trim('$1')).'">$1</a>', $content);

    // Process nowiki(항상 마지막)
    $content = preg_replace('/(?<!{{{){{{(.*?)}}}$/s', '<xmp>$1</xmp>', $content);

    // XSS 방지 (script, img)
    $content = preg_replace('/(<|&lt;)script(.*?)script(>|&gt;)/s', '<xmp>$0<\/xmp>', $content);
    $content = preg_replace('/(<|&lt;)img(.*?)(>|&gt;)/s', '<xmp>$0<\/xmp>', $content);

    // 틀(수정요)
    $content = preg_replace('/(?<!{{{){{틀:(.*?)}}$/s', '<xmp>$1</xmp>', $content);
    $content = preg_replace('/(?<!{{{){{유튜브:(.*?)}}$/s', '<iframe width="560" height="315" src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>', $content);

    // 공백
    $content = preg_replace('/\n/', '<br>', $content);
    return $content;
}
?>
