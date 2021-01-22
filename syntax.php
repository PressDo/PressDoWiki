<?php
include 'config.php';
global $conf;
/*
PressDo Wiki Syntax Processor
*/
function readSyntax($content)
{

    // 라이브러리를 불러옵니다.
    require_once("./NamuMark.php");
    require_once("./NamuMark/NamuMarkExtra.php");
    require_once("./NamuMark/php-namumark.php");
    require_once("./NamuMark/php-namumark.class1.php");
    require_once("./NamuMark/php-namumark.class2.php");
    require_once("./NamuMark/php-namumark.class3.php");

    // MySQLWikiPage와는 달리 PlainWikiPage의 첫 번째 인수로 위키텍스트를 받습니다.
    $wPage = new PlainWikiPage($content);

    // NamuMark 생성자는 WikiPage를 인수로 받습니다.
    $wEngine = new NamuMark($wPage);

    // 위키링크의 앞에 붙을 경로를 prefix에 넣습니다.
    $wEngine->prefix = $conf['Domain'].'index.php?title=';

    // toHtml을 호출하면 HTML 페이지가 생성됩니다.
    $content = $wEngine->toHtml();


    $title = '대문';
    # 보조 파서를 불러온다.
	$Extra = new NamuMarkExtra($content, $title);
    $Extra->title();
	$mediawikiTable = $Extra->cutMediawikiTable();
	$Extra->table();
    $Extra->indent();
    $Extra->getTemplateParameter();
    $text = $Extra->text;

	# 파서를 불러온다.
	$wEngine = new NamuMark1($text, $title);
    $text =  $wEngine->toHtml();
    
    preg_match_all('/<html>(.*?)<\/html>/s', $text, $html);
    require_once './NamuMark/XSSfilter.php';
    foreach ($html[1] as $code) {
        $lines = explode("\n", $code);
        $code_ex = '';
        foreach($lines as $key => $line) {
            if( (!$key && !$lines[$key]) || ($key == count($lines) - 1 && !$lines[$key]) )
                continue;
            if (preg_match('/^(:+)/', $line, $match)) {
                $line = substr($line, strlen($match[1]));
                $add = '';
                for ($i = 1; $i <= strlen($match[1]); $i++)
                    $add .= ' ';
                $line = $add . $line;
                $code_ex .= $line . "\n";
            } else {
                if(!isset($lines[$key + 1]) || $lines[$key + 1] === '')
                    $code_ex .= $line;
                else
                    $code_ex .= $line . "\n";
            }
        }
        $xss = new XssHtml($code_ex);
        $text = str_replace($code, $xss->getHtml(), $text);
    }

    $Extra = new NamuMarkExtra($text, $title);
    $Extra->pasteMediawikiTable($mediawikiTable);
    $text = $Extra->text;

    function NamuMarkHTML( Parser &$parser, &$text ) {
        $title = $parser->getTitle();
        if (!preg_match('/^특수:/', $title) && !preg_match("/&action=history/", $_SERVER["REQUEST_URI"]) && !preg_match('/^사용자:.*\.(css|js)$/', $title)) {
            $text = str_replace('&apos;', "'", $text);
            $text = str_replace('&gt;', ">", $text);
    
            $Extra = new NamuMarkExtra($text, $title);
            $mediawikiTable = $Extra->cutMediawikiTable();
            $Extra->table();
            $text = $Extra->text;
    
            # 파서를 불러온다.
            $wEngine = new NamuMark2($text, $title);
            $text =  $wEngine->toHtml();
    
            $Extra = new NamuMarkExtra($text, $title);
            $Extra->pasteMediawikiTable($mediawikiTable);
            $text = $Extra->text;
    
        }
    
    }

    function NamuMarkHTML2( &$parser, &$text ) {
        $title = $parser->getTitle();
        if (!preg_match('/^특수:/', $title) && !preg_match("/&action=history/", $_SERVER["REQUEST_URI"]) && !preg_match('/^사용자:.*\.(css|js)$/', $title)) {
            $text = str_replace("<br /></p>\n<p>", '<br />', $text);
            $text = str_replace("<p><br />\n</p>", '', $text);
    
            $text = preg_replace('/<a rel="nofollow" target="_blank" class="external autonumber" href="(.*?)">\[(\[\d+\])\]<\/a>/',
            '<a rel="nofollow" target="_blank" class="external autonumber" href="$1">$2</a>',
            $text);
    
            $text = preg_replace('@^<ol><li><ol><li>.*?</li></ol></li></ol>$@ms', '', $text);
    
            $Extra = new NamuMarkExtra($text, $title);
            $Extra->enter();
            $text = $Extra->text;
        }
    }
    
    function NamuMarkExtraHTML ( &$parser, &$text ) {
        $title = $parser->getTitle(); // 문서의 제목을 title로 변수화한다.
    
        if (!preg_match('/^특수:/', $title) && !preg_match("/&action=history/", $_SERVER["REQUEST_URI"]) && !preg_match('/^사용자:.*\.(css|js)$/', $title)) {
            $Extra = new NamuMarkExtra($text, $title);
            preg_match('/(<div id="specialchars".*<\/div>)/s', $text, $charinsert);
            $text = preg_replace('/(<div id="specialchars".*<\/div>)/s', '', $text);
            $Extra->external();
            $Extra->imageurl();
            $Extra->printTemplateParameter();
            $text = $Extra->text;
        }
    }
    //$content = new NamuMarkHTML($content);

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
