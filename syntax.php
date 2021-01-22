<?php
include 'config.php';
global $conf;
/*
PressDo Wiki Syntax Processor
*/
function readSyntax($content)
{
    /* NamuMark PHP Library by koreapyj */
    // 라이브러리를 불러옵니다.
    require_once("./NamuMark.php");

    // MySQLWikiPage와는 달리 PlainWikiPage의 첫 번째 인수로 위키텍스트를 받습니다.
    $wPage = new PlainWikiPage($content);

    // NamuMark 생성자는 WikiPage를 인수로 받습니다.
    $wEngine = new NamuMark($wPage);

    // 위키링크의 앞에 붙을 경로를 prefix에 넣습니다.
    $wEngine->prefix = $conf['Domain'].'index.php?title=';

    // toHtml을 호출하면 HTML 페이지가 생성됩니다.
    $content = $wEngine->toHtml();


    $title = '대문';

    // 각주 크기
    $content = preg_replace('/<a id=\"rfn-(.*?)<\/a>/s', '<sup>$0</sup>', $content);

    // 볼드체
    $content = preg_replace('/(?<!{{{)\*\*(.*?)\*\*/s', '<b>$1</b>', $content);
    
    // 이탤릭(반드시 볼드체보다 나중에)
    $content = preg_replace('/(?<!{{{)\*(.*?)\*/s', '<i>$1</i>', $content);

    // 주석
    $content = preg_replace('/^\/\/(.*?)$/m', '', $content);
    $content = preg_replace('/(?<!{{{)\/\*(.*?)\*\//s', '', $content);

    // XSS 방지 (script, img)
    $content = preg_replace('/(<|&lt;)script(.*?)script(>|&gt;)/s', '<xmp>$0<\/xmp>', $content);
    $content = preg_replace('/(<|&lt;)img(.*?)(>|&gt;)/s', '<xmp>$0<\/xmp>', $content);

    // 틀(수정요)
    $content = preg_replace('/(?<!{{{){{틀:(.*?)}}/s', '<xmp>$1</xmp>', $content);
    $content = preg_replace('/(?<!{{{){{유튜브:(.*?)}}/s', '<iframe width="560" height="315" src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>', $content);

     return $content;
}
?>
