<?php
$conf = json_decode(file_get_contents(__DIR__.'/data/global/config.json'), true);
/*
PressDo Wiki Syntax Processor
*/
function readSyntax($content, $noredirect = 0)
{
    global $conf;

    if(preg_match('/^#(redirect|넘겨주기) (.*)/', $content, $rd))
        if($noredirect !== 1)
            Header('Location: http://'.$conf['Domain'].$conf['ViewerUri'].$rd[2]);
        
    $content = preg_replace('/[^-]*-{4,9}[^-]*/', '[@@@PressDo-Replace-hr@@@]', $content);

    /* NamuMark PHP Library by koreapyj */
    // 라이브러리를 불러옵니다.
    require_once("NamuMark.php");

    // MySQLWikiPage와는 달리 PlainWikiPage의 첫 번째 인수로 위키텍스트를 받습니다.
    $wPage = new PlainWikiPage($content);

    // NamuMark 생성자는 WikiPage를 인수로 받습니다.
    $wEngine = new NamuMark($wPage);

    // 위키링크의 앞에 붙을 경로를 prefix에 넣습니다.
    $wEngine->prefix = $conf['ViewerUri'];

    // toHtml을 호출하면 HTML 페이지가 생성됩니다.
    $content = $wEngine->toHtml(); 
    
    // 수평줄
    $content = str_replace('[@@@PressDo-Replace-hr@@@]', '<hr>', $content);

    // 각주 크기
    $content = preg_replace('/<a id=\"rfn-(.*?)<\/a>/s', '<sup>$0</sup>', $content);

    // 볼드체
    $content = preg_replace('/(?<!{{{)\*\*(.*?)\*\*/s', '<b>$1</b>', $content);
    

    // 주석
    $content = preg_replace('/^\/\/(.*?)$/m', '', $content);
    $content = preg_replace('/(?<!{{{)\/\*(.*?)\*\//s', '', $content);

    // XSS 방지 (script, img)
    $content = preg_replace('/(<|&lt;)script(.*?)script(>|&gt;)/s', '<xmp>$0<\/xmp>', $content);
    $content = preg_replace('/(<|&lt;)img(.*?)(>|&gt;)/s', '<xmp>$0<\/xmp>', $content);
    
    return $content;
}
?>
