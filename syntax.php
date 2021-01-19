<?php
/*
PressDo Wiki Syntax Processor
*/
function readSyntax($content)
{
    // nowiki 이스케이프 정규식 (?<!{{{)
    // https://www.phpliveregex.com/#tab-preg-replace
    // 각주
    $content = preg_replace('/((?<!{{{)\[\* (.*?)\])/', '<ref>$2</ref>', $content);

    // 볼드체
    $content = preg_replace('/(?<!{{{)\*\*(.*?)\*\*/', '<b>$1</b>', $content);
    $content = preg_replace('/(?<!{{{)\'\'\'(.*?)\'\'\'/', '<b>$1</b>', $content);

    // 이탤릭(반드시 볼드체보다 나중에)
    $content = preg_replace('/(?<!{{{)\*(.*?)\*/', '<i>$1</i>', $content);
    $content = preg_replace('/(?<!{{{)\'\'(.*?)\'\'/', '<i>$1</i>', $content);

    // 취소선
    $content = preg_replace('/(?<!{{{)~~(.*?)~~/', '<del>$1</del>', $content);
    $content = preg_replace('/(?<!{{{)--(.*?)--/', '<del>$1</del>', $content);

    // 글머리
    $content = preg_replace('/^ \* (.*?)$/m', '<li>$1</li>', $content);

    // 주석
    $content = preg_replace('/^#(.*?)$/m', '', $content);
    $content = preg_replace('/^\/\/(.*?)$/m', '', $content);
    $content = preg_replace('/(?<!{{{)\/\*(.*?)\*\//s', '', $content);

    // Process nowiki(항상 마지막)
    $content = preg_replace('/(?<!{{{){{{(.*?)}}}$/', '<xmp>$1</xmp>', $content);

    // 틀(수정요)
    $content = preg_replace('/(?<!{{{){{(.*?)}}$/', '<xmp>$1</xmp>', $content);
    return $content;
}
?>