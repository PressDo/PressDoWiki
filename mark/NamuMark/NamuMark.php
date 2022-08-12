<?php
/*
 * namumark.php - Namu Mark Renderer
 * Copyright (C) 2015 koreapyj koreapyj0@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * :::::::::::: ORIGINAL CODE: koreapyj, 김동동(1st edited) ::::::::::::
 * :::::::::::::::::::::: 2nd Edited by PRASEOD- ::::::::::::::::::::::
 * 코드 설명 주석 추가: PRASEOD-
 * 설명 주석이 +로 시작하는 경우 PRASEOD-의 2차 수정 과정에서 추가된 코드입니다.
 *
 * ::::::::: 변경 사항 ::::::::::
 * 카카오TV 영상 문법 추가 [나무위키]
 * 문단 문법 미작동 문제 수정
 * 일부 태그 속성 수정
 * 글씨크기 관련 문법 수정
 * {{{#!wiki }}} 문법 오류 수정
 * anchor 문법 추가
 * 테이블 파서 재설계
 * 수평선 문법 미작동 및 개행 오류 수정
 * <nowiki>, <pre> 태그 <code>로 대체
 * 취소선 태그 <s>에서 <del>로 변경
 * 본문 영역 문단별 <div> 적용
 * 접힌목차 기능 추가
 */
class PlainWikiPage {
    // 평문 데이터 호출
    public $title, $text, $lastchanged;
    function __construct($text) {
        $this->title = '(inline wikitext)';
        $this->text = $text;
        $this->lastchanged = time();
    }
}

class NamuMark {
    public $prefix, $lastchange;

    function __construct($wtext) {
        // 문법 데이터 생성
        $this->list_tag = [
            ['*', 'ul data-pressdo-ul'],
            ['1.', 'ol data-pressdo-ol data-pressdo-ol-numeric'],
            ['A.', 'ol data-pressdo-ol data-pressdo-ol-capitalised'],
            ['a.', 'ol data-pressdo-ol data-pressdo-ol-alphabetical'],
            ['I.', 'ol data-pressdo-ol data-pressdo-ol-caproman'],
            ['i.', 'ol data-pressdo-ol data-pressdo-ol-lowroman']
        ];

        $this->h_tag = [
            ['/^======#? (.*) #?======/', 6],
            ['/^=====#? (.*) #?=====/', 5],
            ['/^====#? (.*) #?====/', 4],
            ['/^===#? (.*) #?===/', 3],
            ['/^==#? (.*) #?==/', 2],
            ['/^=#? (.*) #?=/', 1]
        ];

        $this->multi_bracket = [
            [
                'open'    => '{{{',
                'close' => '}}}',
                'multiline' => true,
                'processor' => [$this,'renderProcessor']
            ]
        ];

        $this->single_bracket = [
            [
                'open'    => '{{{',
                'close' => '}}}',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => '[[',
                'close' => ']]',
                'multiline' => false,
                'processor' => [$this,'linkProcessor']
            ],
            [
                'open'    => '[',
                'close' => ']',
                'multiline' => false,
                'processor' => [$this,'macroProcessor']
            ],

            [
                'open'    => '\'\'\'',
                'close' => '\'\'\'',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => '\'\'',
                'close' => '\'\'',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => '**',
                'close' => '**',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => '~~',
                'close' => '~~',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => '--',
                'close' => '--',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => '__',
                'close' => '__',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => '^^',
                'close' => '^^',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ],
            [
                'open'    => ',,',
                'close' => ',,',
                'multiline' => false,
                'processor' => [$this,'textProcessor']
            ]
        ];

        $this->cssColors = [
            'black','gray','grey','silver','white','red','maroon','yellow','olive','lime','green','aqua','cyan','teal','blue','navy','magenta','fuchsia','purple',
            'dimgray','dimgrey','darkgray','darkgrey','lightgray','lightgrey','gainsboro','whitesmoke',
            'brown','darkred','firebrick','indianred','lightcoral','rosybrown','snow','mistyrose','salmon','tomato','darksalmon','coral','orangered','lightsalmon',
            'sienna','seashell','chocolate','saddlebrown','sandybrown','peachpuff','peru','linen','bisque','darkorange','burlywood','anaatiquewhite','tan','navajowhite',
            'blanchedalmond','papayawhip','moccasin','orange','wheat','oldlace','floralwhite','darkgoldenrod','goldenrod','cornsilk','gold','khaki','lemonchiffon',
            'palegoldenrod','darkkhaki','beige','ivory','lightgoldenrodyellow','lightyellow','olivedrab','yellowgreen','darkolivegreen','greenyellow','chartreuse',
            'lawngreen','darkgreen','darkseagreen','forestgreen','honeydew','lightgreen','limegreen','palegreen','seagreen','mediumseagreen','springgreen','mintcream',
            'mediumspringgreen','mediumaquamarine','aquamarine','turquoise','lightseagreen','mediumturquoise','azure','darkcyan','darkslategray','darkslategrey',
            'lightcyan','paleturquoise','darkturquoise','cadetblue','powderblue','lightblue','deepskyblue','skyblue','lightskyblue','steelblue','aliceblue','dodgerblue',
            'lightslategray','lightslategrey','slategray','slategrey','lightsteelblue','comflowerblue','royalblue','darkblue','ghostwhite','lavender','mediumblue',
            'midnightblue','slateblue','darkslateblue','mediumslateblue','mediumpurple','rebeccapurple','blueviolet','indigo','darkorchid','darkviolet','mediumorchid',
            'darkmagenta','plum','thistle','violet','orchid','mediumvioletred','deeppink','hotpink','lavenderblush','palevioletred','crimson','pink','lightpink'
        ];
        $this->videoURL = [
            'youtube' => '//www.youtube.com/embed/',
            'kakaotv' => '//tv.kakao.com/embed/player/cliplink/',
            'nicovideo' => '//embed.nicovideo.jp/watch/',
            'navertv' => '//tv.naver.com/embed/',
            'vimeo' => '//player.vimeo.com/video/'
        ];

        $this->macro_processors = [];

        $this->WikiPage = $wtext;
        $this->imageAsLink = false;
        $this->wapRender = false;

        $this->toc = array();
        $this->fn = array();
        $this->category = array();
        $this->links = [];
        $this->fn_cnt = 0;
        $this->prefix = '';
    }

    public function includePage($title){
        list($rawns, $namespace, $title) = WikiDocs::parse_title($title, $this->ns);
        $a = $this->db->prepare("SELECT d.`content` FROM `document` AS d INNER JOIN `live_document_list` AS l ON d.docid = l.docid WHERE l.namespace=? AND l.title=? LIMIT 1");
        $a->execute([$namespace,$title]);
        return new PlainWikiPage($a->fetch(PDO::FETCH_ASSOC)['content']);
    }
    
    public function pageExists($title){
        list($rawns, $namespace, $title) = WikiDocs::parse_title($title, $this->ns);
        $a = $this->db->prepare("SELECT count(*) as cnt FROM `live_document_list` WHERE namespace=? AND title=?");
        $a->execute([$namespace,$title]);
        return new PlainWikiPage($a->fetch(PDO::FETCH_ASSOC)['content']);
    }

    public function toHtml() {
        // 문법을 HTML로 변환하는 함수
        if(empty($this->WikiPage->title))
            return '';
        $this->whtml = $this->WikiPage->text;
        $this->whtml = $this->htmlScan($this->whtml);
        return '<div data-pressdo-doc-paragraph>'.$this->whtml.'</div>';
    }

    private function htmlScan($text) {
        $result = '';
        $len = strlen($text);
        $now = '';
        $line = '';

        // 리다이렉트 문법
        if(self::startsWith($text, '#') && preg_match('/^#(?:redirect|넘겨주기) (.+)$/im', $text, $target) && $this->inThread !== false) {
            array_push($this->links, ['target'=>$target[1], 'type'=>'redirect']);
            @header('Location: '.$this->uriset['wiki'].'/'.self::encodeURI($target[1]));
            return;
        }

        // 문법 처리 순서: 리스트 > 인용문 > 삼중괄호 > 표 >
        for($i=0;$i<$len && $i>=0;self::nextChar($text,$i)) {
            $now = self::getChar($text,$i);
            
            //+ 백슬래시 문법
            if($now == "\\"){
                ++$i;
                $line .= self::getChar($text,$i);
                ++$i;
                $now = self::getChar($text,$i);
            }
            
            if($line == '' && $now == ' ' && $list = $this->listParser($text, $i)) {
                $result .= $list;
                $line = '';
                $now = '';
                continue;
            }

            // 인용문
            if($line == '' && self::startsWith($text, '&gt;', $i) && $blockquote = $this->bqParser($text, $i)) {
                $result .= $blockquote;
                $line = '';
                $now = '';
                continue;
            }

            foreach($this->multi_bracket as $bracket) {
                if(self::startsWith($text, $bracket['open'], $i) && $innerstr = $this->bracketParser($text, $i, $bracket)) {
                    $result .= $this->lineParser($line).$innerstr;
                    $line = '';
                    $now = '';
                    break;
                }
            }

            // 표
            if($line == '' && self::startsWith($text, '|', $i) && $table = $this->tableParser($text, $i)) {
                $result .= $table;
                $line = '';
                $now = '';
                continue;
            }

            if($now == "\n") { // line parse
                $result .= $this->lineParser($line);
                $line = '';
            }
            else
                $line.=htmlspecialchars($now);
        }
        if($line != '')
            $result .= $this->lineParser($line);

        $result .= $this->printFootnote();

        // 분류 모음
        // + HTML 구조 약간 변경함
        if(!empty($this->category) && $this->inThread !== false) {
            $result .= '<div id="categories" class="wiki-categories">
                            <h2>분류</h2>
                            <ul>';
            foreach($this->category as $category) {
                $result .= '<li class="wiki-categories">'.$this->linkProcessor(':분류:'.$category.'|'.$category, '[[').'</li>';
            }
            $result .= '</ul></div>';
        }
        return $result;
    }

    private function bqParser($text, &$offset) {
        /*$len = strlen($text);        
        $innerhtml = '';
        for($i=$offset;$i<$len;$i=self::seekEndOfLine($text, $i)+1) {
            $eol = self::seekEndOfLine($text, $i);
            if(!self::startsWith($text, '&gt;', $i)) {
                // table end
                break;
            }
            $i+=4;
            $line = $this->formatParser(substr($text, $i, $eol-$i));
            $line = preg_replace('/^(&gt;)+/', '', $line);
            if($this->wapRender)
                $innerhtml .= $line.'<br/>';
            else
                $innerhtml .= '<p>'.$line.'</p>';
        }
        if(empty($innerhtml))
            return false;

        $offset = $i-1;
        return '<blockquote class="_blockquote">'.$innerhtml.'</blockquote>';*/
        /*$temp = [];
        $_wlen = iconv_strlen($text);
        $init = true;
        for($i=$offset;$i<$_wlen;$i=self::seekEndOfLine($text, $i)+1){
            // 매 루프마다 i값을 다음 줄의 첫 글자로 넘김
            $eol = self::seekEndOfLine($text, $i);
            
            // 첫 글자가 >가 아닐 경우 (인용문 끝)
            if(!str_starts_with(iconv_substr($wikitext, $i), '>'))
                break;
            preg_match('/^>+/', iconv_substr($wikitext,$i), $bq_match);
            $level = iconv_strlen($bq_match[0]); // >의개수
            
            // i + level: >다음의 첫글자
            $line = iconv_substr($wikitext, $i+$level, $eol - $level - $i + 1);
            array_push($temp, array('level' => $level, 'line' => $line));
        }
        if(count($temp) == 0)
            return null;
        $curLevel = 1;
        $result = '<blockquote class="wiki-quote">';
        foreach($temp as $curTemp){
            // 다중 인용문
            if($curTemp['level'] > $curLevel){
                $_clvcalc = $curTemp['level'] - $curLevel;
                for($i=0; $i<$_clvcalc; $i++)
                    $result .= '<blockquote class="wiki-quote">';
            } elseif($curTemp['level'] < $curLevel){
                $_clvcalc = $curLevel - $curTemp['level'];
                for($i=0; $i<$_clvcalc; $i++)
                    $result .= '</blockquote>';
            } else
                $result .= '<br>';
            
            array_push($result, array('name' => 'wikitext', 'parseFormat' => true, 'text' => $curTemp['line']));
        }
        array_push($result, array('name' => 'blockquote-end'));
        $setpos($i-1);*/
        return $result;
    }

    protected static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    protected function tableParser($text, &$offset) {
        $token = ['caption' => null, 'colstyle' => [], 'rows' => []];
        $tableinit = true;
        $tableAttr = [];
        $tdAttr = [];
        $tableattrinit = [];
        $tableAttrStr = '';
        $trAttrStr = '';
        $tableInnerStr = '';
        $trInnerStr = '';
        $tdInnerStr = '';
        $tdAttrStr = '';
        $len = strlen($text);
        $i = $offset;
        $noheadmark = true;
        $intd = true;
        $rowIndex = 0;
        $colIndex = 0;
        $chpos = function($now, &$i) {
            if(strlen($now) > 1){
                $i += strlen($now) - 1;
            }
        };

        // caption 파싱은 처음에만
        if(self::startsWith($text, '|', $i) && !self::startsWith($text, '||', $i) && $tableinit === true) {
            $caption = explode('|', substr($text,$i));
            if(count($caption) < 3)
                return false;
            $token['caption'] = $this->blockParser($caption[1]);
            $hasCaption = true;
            $tableinit = false;
            //   (|)   (caption content)   (|)
            $i += 1 + strlen($caption[1]) + 1;
        }elseif(self::startsWith($text, '||', $i) && $tableinit === true){
            $i += 2;
            $hasCaption = false;
            $tableinit = false;
        }elseif($tableinit === true)
            return false;

        // text 변수에는 표 앞부분의 ||가 제외된 상태의 문자열이 있음
        /*
        * DOM 구조
        {
            table:[
                'caption': 'caption',
                'colstyles' => []
                'rows' => [
                    [
                        'style' => ['style' => 'style'],
                        'cols' => [
                            ['text' => blockParser, 'style' => ['style' => 'style'], 'rowspan' => 1],
                            'span',
                            [...]
                        ]
                    ],
        */
        for($i; $i<$len; ++$i){
            $now = self::getChar($text,$i);
            
            //+ 백슬래시 문법
            if($now == "\\"){
                ++$i;
                $tdInnerStr .= self::getChar($text,$i);
                $chpos($now, $i);
                continue;
            }elseif($noheadmark === false && $tdInnerStr == '' && $now == ' ' && $list = $this->listParser($text, $i)) {
                $tdInnerStr .= $list;
                continue;
            }elseif(self::startsWith($text, '||', $i)) {
                //var_dump($tdInnerStr);
                if($intd === true){
                    //td end and new td start
                    $token['rows'][$rowIndex]['cols'][$colIndex] = ['text' => $this->blockParser($tdInnerStr), 'style' => $tdAttr];
                    $tdInnerStr = '';
                    ++$colIndex;
                    ++$i;
                    continue;
                }elseif($intd === false){
                    // new td start
                    $intd = true;
                    ++$i;
                    continue;
                }
                continue;
            }elseif($noheadmark === false && $tdInnerStr == '' && self::startsWith($text, '>', $i) && $blockquote = $this->bqParser($text, $i)) {
                $tdInnerStr .= $blockquote;
                continue;
            }elseif($tdInnerStr == '' && self::startsWith($text, '<', $i) && preg_match('/^<([^ ]+)>/', substr($text,$i, self::seekEndOfLine($text,$i) - $i), $match)){
                $attrs = explode('><', $match[1]);
                foreach ($attrs as $attr){
                    $attr = strtolower($attr);
                    if(preg_match('/^([^=]*)=([^=]*)$/', $attr, $tbattr)){
                        // 속성은 최초 설정치가 적용됨

                        if(
                            !in_array(strtr($tbattr[1], ' ', ''), $tableattrinit) && (
                                (in_array($tbattr[1], ['tablealign', 'table align']) && in_array($tbattr[2], ['center', 'left', 'right'])) ||
                                (in_array($tbattr[1], ['tablewidth', 'table width']) && preg_match('/^-?[0-9.]*(px|%)$/', $tbattr[2])) || 
                                (in_array($tbattr[1], ['tablebgcolor', 'table bgcolor', 'tablecolor', 'table color', 'tablebordercolor', 'table bordercolor']) && (in_array($tbattr[2], $this->cssColors) || preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $tbattr[2])))
                            )
                        ){
                            // 표 속성
                            $i += strlen($tbattr[0]) + 8;
                            array_push($tableattrinit, strtr($tbattr[1], ' ', ''));
                            switch(strtr($tbattr[1], ' ', '')){
                                case 'tablebgcolor':
                                    $tbAttrNm = 'background-color';
                                    break;
                                case 'tablecolor':
                                    $tbAttrNm = 'color';
                                    break;
                                case 'tablebordercolor':
                                    $tbAttrNm = 'border-color';
                                    break;
                                case 'tablebgcolor':
                                    $tbAttrNm = 'background-color';
                                    break;
                                case 'tablewidth':
                                    $tbAttrNm = 'width';
                                    break;
                                default:
                                    $tbAttrNm = $tbattr[1];
                            }
                            if(in_array($tbattr[1], ['tablealign', 'table align']))
                                $tbClassStr = ' table-'.$tbattr[2];
                            else
                                $tableAttr[$tbAttrNm] = $tbattr[2];
                        }elseif(
                            // 개별 행 속성
                            in_array($tbattr[1], ['rowbgcolor', 'rowcolor']) && 
                            (in_array($tbattr[2], $this->cssColors) || preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $tbattr[2]))
                        ){
                            $i += strlen($tbattr[0]) + 8;
                            switch($tbattr[1]){
                                case 'rowbgcolor':
                                    $tbAttrNm = 'background-color';
                                    break;
                                case 'rowcolor':
                                    $tbAttrNm = 'color';
                                    break;
                                default:
                                    $tbAttrNm = $tbattr[1];
                            }
                            $token['rows'][$rowIndex]['style'][$tbAttrNm] = $tbattr[2];
                        }elseif(
                            // 개별 열 속성
                            in_array($tbattr[1], ['colbgcolor', 'colcolor']) && 
                            (in_array($tbattr[2], $this->cssColors) || preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $tbattr[2]))
                        ){
                            $i += strlen($tbattr[0]) + 8;
                            switch($tbattr[1]){
                                case 'colbgcolor':
                                    $tbAttrNm = 'background-color';
                                    break;
                                case 'colcolor':
                                    $tbAttrNm = 'color';
                                    break;
                                default:
                                    $tbAttrNm = $tbattr[1];
                            }
                            $token['colstyle'][$colIndex][$tbAttrNm] = $tbattr[2];
                        }elseif(
                            // 개별 셀 속성
                            (in_array($tbattr[1], ['width', 'height']) && preg_match('/^-?[0-9.]*(px|%)$/', $tbattr[2])) ||
                            (in_array($tbattr[1], ['color', 'bgcolor']) && (in_array($tbattr[2], $this->cssColors) || preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $tbattr[2])))
                        ){
                            $i += strlen($tbattr[0]) + 8;
                            switch($tbattr[1]){
                                case 'bgcolor':
                                    $tbAttrNm = 'background-color';
                                    break;
                                default:
                                    $tbAttrNm = $tbattr[1];
                            }
                            $tdAttr[$tbAttrNm] = $tbattr[2];
                        }
                    }elseif(preg_match('/^(-|\|)([0-9]*)$/', $attr, $tbspan)){
                        $i += strlen($tbspan[0]) + 8;
                        // <^|n>
                        if($tbspan[1] == '-')
                            $rowspan = $tbspan[2];
                        elseif($tbspan[1] == '|')
                            $tdAttr['colspan'] = $tbspan[2];
                    }elseif(preg_match('/^(\^|v|)\|([0-9]*)$/', $attr, $tbalign)){
                        $i += strlen($tbalign[0]) + 8;
                        // <^|n>
                        if($tbalign[1] == '^')
                            $tdAttr['vertical-align'] = 'top';
                        elseif($tbalign[1] == 'v')
                            $tdAttr['vertical-align'] = 'bottom';
                        
                        $rowspan = $tbalign[2];
                    }else{
                        // <:>
                        switch($attr){
                            case ':':
                                $tdAttr['text-align'] = 'center';
                                $i += 11;
                                break;
                            case '(':
                                $tdAttr['text-align'] = 'left';
                                $i += 11;
                                break;
                            case ')':
                                $tdAttr['text-align'] = 'right';
                                $i += 11;
                        }
                    }
                }
            }else{
                // bracket
                foreach($this->multi_bracket as $bracket) {
                    if(self::startsWith($text, $bracket['open'], $i) && $innerstr = $this->bracketParser($text, $i, $bracket)) {
                        $tdInnerStr .= $this->lineParser($tdInnerStr).$innerstr;
                        continue;
                    }
                }

                //+ \r과 \r\n 모두에서 작동할 수 있도록 함.
                if((self::startsWith($text, "\r\n||", $i) || self::startsWith($text, "\n||", $i)) && $tdInnerStr == '') {
                    ++$rowIndex;
                    $colIndex = 0;
                    $noheadmark = true;
                    $intd = false;
                }elseif((self::startsWith($text, "\r\n", $i) || self::startsWith($text, "\n", $i)) && self::getChar($text,$i+1) !== '|' && $tdInnerStr == '') {
                    ++$i;
                    break;
                }elseif(self::startsWith($text, "\r\n", $i) || self::startsWith($text, "\n", $i)) {
                    // just breaking line
                    $tdInnerStr .= $now;
                    $noheadmark = false;
                }else{
                    // other string
                    $tdInnerStr.=$now;
                    $noheadmark = true;
                }
            }
            $chpos($now, $i);
        }

        foreach ($token['rows'] as $r){
            if(!is_array($r))
                return false;
            foreach ($r['cols'] as $rc){
                if($rc == 'span')
                    continue;
                if(!empty($rc['style'])){
                    $rcCount = count($rc['style']);
                    $rcKeys = array_keys($rc['style']);
                    for($k=0; $k<$rcCount; ++$k){
                        if($k !== 0)
                        $tdAttrStr .= ' ';
                        $tdAttrStr .= $rcKeys[$k].':'.$rc['style'][$rcKeys[$k]].';';
                    }
                }
                    if(strlen($tdAttrStr) > 0)
                        $tdAttrStr = ' style="'.$tdAttrStr.'"';
                    if(isset($rc['rowspan']))
                        $tdAttrStr .= ' rowspan="'.$rc['rowspan'].'"';
                    if(isset($rc['colspan']))
                        $tdAttrStr .= ' colspan="'.$rc['colspan'].'"';
                    $trInnerStr .= '<td'.$tdAttrStr.'>'.$rc['text'].'</td>';
                    $tdAttrStr = '';
            }
            if(!empty($r['style'])){
                $attrlen = count($r['style']);
                $attkeys = array_keys($r['style']);
                for($k=0; $k<$attrlen; ++$k){
                    $trAttrStr .= $attkeys[$k].':'.$r['style'][$attkeys[$k]].'; ';
                }
            }
            if(strlen($trAttrStr) > 0)
                $trAttrStr = ' style="'.$trAttrStr.'"';
            
            $tableInnerStr .= '<tr'.$trAttrStr.'>'.$trInnerStr.'</tr>';
            $trInnerStr = $trAttrStr = '';
        }
        
        $attrlen = count($tableAttr);
        $attkeys = array_keys($tableAttr);
        for($k=0; $k<$attrlen; ++$k){
            $tableAttrStr .= $attkeys[$k].':'.$tableAttr[$attkeys[$k]].'; ';
        }
        if(strlen($tableAttrStr) > 0)
            $tableAttrStr = ' style="'.$tableAttrStr.'"';
        if(!isset($tbClassStr))
            $tbClassStr = '';
        
        $offset = $i;
        return '<div class="wiki-table-wrap'.$tbClassStr.'"><table class="wiki-table" '.$tableAttrStr.'>'.$tableInnerStr.'</table></div>';
    }

    // 리스트 생성
    private function listParser($text, &$offset) {
        $listTable = array();
        $len = strlen($text);
        $lineStart = $offset;

        $quit = false;
        for($i=$offset;$i<$len;$before=self::nextChar($text,$i)) {
            $now = self::getChar($text,$i);
            if($now == "\n" && empty($listTable[0])) {
                    return false;
            }
            if($now != ' ') {
                if($lineStart == $i) {
                    // list end
                    break;
                }

                $match = false;

                foreach($this->list_tag as $list_tag) {
                    if(self::startsWith($text, $list_tag[0], $i)) {

                        if(!empty($listTable[0]) && $listTable[0]['tag']=='indent') {
                            $i = $lineStart;
                            $quit = true;
                            break;
                        }

                        $eol = self::seekEndOfLine($text, $lineStart);
                        $tlen = strlen($list_tag[0]);
                        $innerstr = substr($text, $i+$tlen, $eol-($i+$tlen));
                        $this->listInsert($listTable, $innerstr, ($i-$lineStart), $list_tag[1]);
                        $i = $eol;
                        $now = "\n";
                        $match = true;
                        break;
                    }
                }
                if($quit)
                    break;

                if(!$match) {
                    // indent
                    if(!empty($listTable[0]) && $listTable[0]['tag']!='indent') {
                        $i = $lineStart;
                        break;
                    }

                    $eol = self::seekEndOfLine($text, $lineStart);
                    $innerstr = substr($text, $i, $eol-$i);
                    $this->listInsert($listTable, $innerstr, ($i-$lineStart), 'indent');
                    $i = $eol;
                    $now = "\n";
                }
            }
            if($now == "\n") {
                $lineStart = $i+1;
            }
        }
        if(!empty($listTable[0])) {
            $offset = $i-1;
            return $this->listDraw($listTable);
        }
        return false;
    }

    // 리스트에 추가
    private function listInsert(&$arr, $text, $level, $tag) {
        if(preg_match('/^#([1-9][0-9]*) /', $text, $start))
            $start = $start[1];
        else
            $start = 1;
        if(empty($arr[0])) {
            $arr[0] = array('text' => $text, 'start' => $start, 'level' => $level, 'tag' => $tag, 'childNodes' => array());
            return true;
        }

        $last = self::count($arr)-1;
        $readableId = $last+1;
        if($arr[0]['level'] >= $level) {
            $arr[] = array('text' => $text, 'start' => $start, 'level' => $level, 'tag' => $tag, 'childNodes' => array());
            return true;
        }

        return $this->listInsert($arr[$last]['childNodes'], $text, $level, $tag);
    }

    // 리스트 생성
    private function listDraw($arr) {
        if(empty($arr[0]))
            return '';

        $tag = $arr[0]['tag'];
        $start = $arr[0]['start'];
        $result = '<'.($tag=='indent'?'div class="indent"':$tag.($start!=1?' start="'.$start.'"':'')).'>';
        foreach($arr as $li) {
            $text = $this->blockParser($li['text']).$this->listDraw($li['childNodes']);
            $result .= $tag=='indent'?$text:'<li>'.$text.'</li>';
        }
        $result .= '</'.($tag=='indent'?'div':$tag).'>';
        return $result;
    }

    private function lineParser($line) {
        $result = '';
        $line_len = strlen($line);

        // 주석
        if(self::startsWith($line, '##')) {
            $line = '';
        }

        // == Title == (문단)
        //+ 공백 있어서 안 되는 오류 수정
        if(self::startsWith($line, '=') && preg_match('/^(=+)(.*?)(=+) *$/', trim($line), $match) && $match[1]===$match[3]) {
            $level = strlen($match[1]);
            $innertext = $this->blockParser($match[2]);

            //+ 접힌문단 기능 추가
                if (preg_match('/^# (.*) #$/', $innertext, $ftoc)) {
                        $folded = 'data-pressdo-toc-fold="hide"';
                        $innertext = $ftoc[1];
                }else{
                $folded = 'data-pressdo-toc-fold="show"';
                }
            $id = $this->tocInsert($this->toc, $innertext, $level);
            $js = 'class="hidden-trigger"';
            $result .= '</div><h'.$level.' class="wiki-heading" '.$js.' '.$folded.' id="s-'.$id.'">
                        <a name="s-'.$id.'" href="#_toc">'.$id.'. </a>';

            //+ 문단에서 앵커가 태그속에 들어가는 부분 수정
            if(preg_match('/\[anchor\((.*)\)\]/', $innertext, $anchor)){
                $RealContent = str_replace($anchor[0], '', $innertext);
                $result .= '<a id="'.$anchor[1].'"></a><span id="'.trim($RealContent).'">'.$RealContent;
            }else{
                $result .= '<span id="'.strip_tags($innertext).'">'.$innertext;
            }

            //+ 부분 편집 기능 작업
            //+ content-s- 속성 추가 (문단 숨기기용)
            $result .= '<span class="wiki-edit-section nm-cb"><a href="'.$this->uriset['edit'].$this->title.$this->uriprefix.'section='.$id.'" rel="nofollow">[편집]</a></span>
                            </span>
                        </h'.$level.'><div id="content-s-'.$id.'" class="wiki-heading-content" '.$folded.'>';
            $line = '';

        }

        //+ 수평줄 문제 개선
        $line = preg_replace('/^[^-]*(-{4,9})[^-]*$/', '<hr class="wiki-hr">', $line);

        $line = $this->blockParser($line);

        // 행에 뭐가 있을때
        if($line != '') {
            if($this->wapRender)
                $result .= $line.'<br><br>';
            elseif(strpos($line, '<hr class="wiki-hr">') !== false)
                $result .= $line;
            else
                $result .= $line.'<br>';
        }

        return $result;
    }

    private function blockParser($block) {
        return $this->formatParser($block);
    }

    private function bracketParser($text, &$now, $bracket) {
        $len = strlen($text);
        $cnt = 0;
        $done = false;

        $openlen = strlen($bracket['open']);
        $closelen = strlen($bracket['close']);

        if(!isset($bracket['strict']))
            $bracket['strict'] = true;

        for($i=$now;$i<$len;self::nextChar($text,$i)) {
            /* + 표 안에서도 #!wiki가 적용되도록 함.
            if($bracket['open'] == '{{{#!wiki')
                $bracket['open'] = '{{{';
            */
            if(self::startsWith($text, $bracket['open'], $i) && !($bracket['open']==$bracket['close'] && $cnt>0)) {
                $cnt++;
                $done = true;
                $i+=$openlen-1; // 반복될 때 더해질 것이므로
            }elseif(self::startsWith($text, $bracket['close'], $i)) {
                $cnt--;
                $i+=$closelen-1;
            }elseif(!$bracket['multiline'] && $text[$i] == "\n")
                return false;

            if($cnt == 0 && $done) {
                $innerstr = substr($text, $now+$openlen, $i-$now-($openlen+$closelen)+1);

                if(($bracket['strict'] && $bracket['multiline'] && strpos($innerstr, "\n")===false))
                    return false;

                $result = call_user_func_array($bracket['processor'],array($innerstr, $bracket['open']));
                $now = $i;
                return $result;
            }
        }
        return false;
    }

    private function formatParser($line) {
        $line_len = strlen($line);
        for($j=0;$j<$line_len;self::nextChar($line,$j)) {
            /*if(self::getChar() == "\\"){
                ++$j;

                continue;
            }*/

            // 외부이미지
            /*if(self::startsWith($line, 'http', $j) && preg_match('/(https?:\/\/[^ ]+\.(jpg|jpeg|png|gif))(?:\?([^ ]+))?/i', $line, $match, 0, $j)) {
                if($this->imageAsLink)
                    $innerstr = '<span class="alternative">[<a class="external" target="_blank" href="'.$match[1].'">image</a>]</span>';
                else {
                    $paramtxt = '';
                    $csstxt = '';
                    if(!empty($match[3])) {
                        preg_match_all('/[&?]?([^=]+)=([^\&]+)/', htmlspecialchars_decode($match[3]), $param, PREG_SET_ORDER);
                        foreach($param as $pr) {
                            // 이미지 크기속성
                            switch($pr[1]) {
                                case 'width':
                                    if(preg_match('/^[0-9]+$/', $pr[2]))
                                        $csstxt .= 'width: '.$pr[2].'px; ';
                                    else
                                        $csstxt .= 'width: '.$pr[2].'; ';
                                    break;
                                case 'height':
                                    if(preg_match('/^[0-9]+$/', $pr[2]))
                                        $csstxt .= 'height: '.$pr[2].'px; ';
                                    else
                                        $csstxt .= 'height: '.$pr[2].'; ';
                                    break;
                                case 'align':
                                    if($pr[2]!='center')
                                        $csstxt .= 'float: '.$pr[2].'; ';
                                    break;
                                default:
                                    $paramtxt.=' '.$pr[1].'="'.$pr[2].'"';
                            }
                        }
                    }
                    $paramtxt .= ($csstxt!=''?' style="'.$csstxt.'"':'');
                    $innerstr = '<img src="'.$match[1].'"'.$paramtxt.'>';
                }
                $line = substr($line, 0, $j).$innerstr.substr($line, $j+strlen($match[0]));
                $line_len = strlen($line);
                $j+=strlen($innerstr)-1;
                continue;
            }else*/
            if(self::startsWith($line, 'attachment', $j) && preg_match('/attachment:([^\/]*\/)?([^ ]+\.(?:jpg|jpeg|png|gif|svg))(?:\?([^ ]+))?/i', $line, $match, 0, $j) && $this->inThread !== false) {
                // 파일
                if($this->imageAsLink)
                    $innerstr = '<span class="alternative">[<a class="external" target="_blank" href="https://attachment.namu.wiki/'.($match[1]?($match[1]=='' || substr($match[1], 0, -1)==''?'':substr($match[1], 0, -1).'__'):rawurlencode($this->WikiPage->title).'__').$match[2].'">image</a>]</span>';
                else {
                    $paramtxt = '';
                    $csstxt = '';
                    if(!empty($match[3])) {
                        preg_match_all('/([^=]+)=([^\&]+)/', $match[3], $param, PREG_SET_ORDER);
                        foreach($param as $pr) {
                            switch($pr[1]) {
                                case 'width':
                                    if(preg_match('/^[0-9]+$/', $pr[2]))
                                        $csstxt .= 'width: '.$pr[2].'px; ';
                                    else
                                        $csstxt .= 'width: '.$pr[2].'; ';
                                    break;
                                case 'height':
                                    if(preg_match('/^[0-9]+$/', $pr[2]))
                                        $csstxt .= 'height: '.$pr[2].'px; ';
                                    else
                                        $csstxt .= 'height: '.$pr[2].'; ';
                                    break;
                                case 'align':
                                    if($pr[2]!='center')
                                        $csstxt .= 'float: '.$pr[2].'; ';
                                    break;
                                default:
                                    $paramtxt.=' '.$pr[1].'="'.$pr[2].'"';
                            }
                        }
                    }
                    $paramtxt .= ($csstxt!=''?' style="'.$csstxt.'"':'');
                    $innerstr = '<img src="https://attachment.namu.wiki/'.($match[1]?($match[1]=='' || substr($match[1], 0, -1)==''?'':substr($match[1], 0, -1).'__'):rawurlencode($this->WikiPage->title).'__').$match[2].'"'.$paramtxt.'>';
                }
                $line = substr($line, 0, $j).$innerstr.substr($line, $j+strlen($match[0]));
                $line_len = strlen($line);
                $j+=strlen($innerstr)-1;
                continue;
            } else {
                foreach($this->single_bracket as $bracket) {
                    $nj=$j;
                    if(self::startsWith($line, $bracket['open'], $j) && $innerstr = $this->bracketParser($line, $nj, $bracket)) {
                        $line = substr($line, 0, $j).$innerstr.substr($line, $nj+1);
                        $line_len = strlen($line);
                        $j+=strlen($innerstr)-1;
                        break;
                    }
                }
                
            }
        }
        return $line;
    }

    protected function renderProcessor($text, $type) {
        $lines = explode("\n", $text);
        // 라인 단위로 끊어 읽기
        $text = '';
        foreach($lines as $key => $line) {
            if( (!$key && !$lines[$key]) || ($key == count($lines) - 1 && !$lines[$key]) )
                continue;
            if (preg_match('/^(:+)/', $line, $match)) {
                $line = substr($line, strlen($match[1]));
                $add = '';
                for ($i = 1; $i <= strlen($match[1]); $i++)
                    $add .= ' ';
                $line = $add . $line;
                $text .= $line . "\n";
            } else {
                $text .= $line . "\n";
            }
        }
        
        // {{{ }}} 처리
        // 코드블럭
        if(self::startsWithi($text, '#!html') && $this->inThread !== false) {
            // HTML
            return '<html>' . preg_replace('/UNIQ--.*?--QINU/', '', substr($text, 7)) . '</html>';
        } elseif(self::startsWithi($text, '#!wiki') && preg_match('/^([^=]+)=(?|"(.*?)"|\'(.*)\'|(.*))/', substr($text, 7), $match)) {
            // + 심화문법
            $text = str_replace($match[0], '', substr($text,7));
            $wPage = new PlainWikiPage($text);
            $wEngine = new NamuMark($wPage);
            $wEngine->noredirect = $this->noredirect;
            $wEngine->prefix = $this->prefix;
            $wEngine->title = $this->title;
            $wEngine->uriset = $this->uriset;
            $wEngine->uriprefix = $this->uriprefix;

            return '<div '.html_entity_decode($match[0]).'>'.htmlspecialchars_decode($wEngine->toHtml()).'</div>';
        } elseif(self::startsWithi($text, '#!syntax') && preg_match('/#!syntax ([^\s]*)/', $text, $match)) {
            // 구문
            return '<syntaxhighlight lang="' . $match[1] . '" line="1">' . preg_replace('/#!syntax ([^\s]*)/', '', $text) . '</syntaxhighlight>';
        } elseif(preg_match('/^\+([1-5])(.*)$/sm', $text, $size)) {
            // {{{+큰글씨}}}

            $lines = explode("\n", $size[2]);
            $size[2] = '';
            foreach($lines as $line) {
                if($line !== '')
                    $size[2] .= $line . "\n";
            }

            if(self::startsWith($size[2], '||')) {
                $offset = 0;
                $size[2] = $this->tableParser($size[2], $offset);
            }

            return '<span class="_text-size-up-'.$size[1].'">'.$this->formatParser($size[2]).'</span>';
        } elseif(preg_match('/^\-([1-5])(.*)$/sm', $text, $size)) {
            // {{{-작은글씨}}}

            $lines = explode("\n", $size[2]);
            $size[2] = '';
            foreach($lines as $line) {
                if($line !== '')
                    $size[2] .= $line . "\n";
            }

            if(self::startsWith($size[2], '||')) {
                $offset = 0;
                $size[2] = $this->tableParser($size[2], $offset);
            }

            return '<span class="_text-size-dn-'.$size[1].'">' . $this->formatParser($size[2]) . '</span>';
        } else {
            return '<pre>' . $text . '</pre>';
            // 문법 이스케이프
        }
    }

    private function linkProcessor($text, $type) {
        $href = explode('|', $text);
        if(!empty($href[1])){
            if(preg_match('/^\[\[파일:(.+)\]\]$/', $href[1], $match) && $this->inThread !== false){
                array_push($this->links, ['target' => $match[1], 'type' => 'file']);
            if($this->imageAsLink)
                return '<span data-pressdo-link-file class="alternative">[<a data-pressdo-link-file target="_blank" title="'.$match[1].'" href="'.self::encodeURI($match[1]).'">image</a>]</span>';

            $paramtxt = '';
            $csstxt = '';
            if(!empty($href[1])) {
                preg_match_all('/[&?]?([^=]+)=([^\&]+)/', htmlspecialchars_decode($href[1]), $param, PREG_SET_ORDER);
                foreach($param as $pr) {
                    switch($pr[1]) {
                        case 'width':
                            if(preg_match('/^[0-9]+$/', $pr[2]))
                                $csstxt .= 'width: '.$pr[2].'px; ';
                            else
                                $csstxt .= 'width: '.$pr[2].'; ';
                            break;
                        case 'height':
                            if(preg_match('/^[0-9]+$/', $pr[2]))
                                $csstxt .= 'height: '.$pr[2].'px; ';
                            else
                                $csstxt .= 'height: '.$pr[2].'; ';
                            break;
                        case 'align':
                            if($pr[2]!='center')
                                $csstxt .= 'float: '.$pr[2].'; ';
                            break;
                        default:
                            $paramtxt.=' '.$pr[1].'="'.$pr[2].'"';
                    }
                }
            }
            $paramtxt .= ($csstxt!=''?' style="'.$csstxt.'"':'');
            }else{
            $href[1] = implode('|', array_slice($href, 1));
            $display_txt = $this->formatParser($href[1]);
            }
        }else
            $display_txt = $href[0];
        if(preg_match('/^https?:\/\//', $href[0])) {
            // [[URL]]
            $targetUrl = $href[0];
            $class = '_extlink l-e';
            $target = '_blank';
        }elseif(preg_match('/^분류:(.+)$/', $href[0], $category) && $this->inThread !== false) {
            // [[분류:분류]]
            array_push($this->links, array('target'=>$category[0], 'type'=>'category'));
            if(!$this->included)
                array_push($this->category, $category[1]);
            return ' ';
        }elseif(preg_match('/^#(.+)$/', $href[0], $category)) {
            //+ [[#anchor]]
            $targetUrl = $href[0];
            return '<a href="'.$targetUrl.'" title="" >'.$display_txt.'</a>';
        }elseif(preg_match('/^파일:(.+)$/', $href[0], $category) && $this->inThread !== false) {
            // [[파일:ㅁㅁ]]
            array_push($this->links, array('target'=>$category[0], 'type'=>'file'));
            if($this->imageAsLink)
                return '<span data-pressdo-link-file class="alternative">[<a data-pressdo-link-file target="_blank" title="'.$category[0].'" href="'.self::encodeURI($category[0]).'">image</a>]</span>';

            $paramtxt = '';
            $csstxt = '';
            if(!empty($href[1])) {
                preg_match_all('/[&?]?([^=]+)=([^\&]+)/', htmlspecialchars_decode($href[1]), $param, PREG_SET_ORDER);
                foreach($param as $pr) {
                    switch($pr[1]) {
                        case 'width':
                            if(preg_match('/^[0-9]+$/', $pr[2]))
                                $csstxt .= 'width: '.$pr[2].'px; ';
                            else
                                $csstxt .= 'width: '.$pr[2].'; ';
                            break;
                        case 'height':
                            if(preg_match('/^[0-9]+$/', $pr[2]))
                                $csstxt .= 'height: '.$pr[2].'px; ';
                            else
                                $csstxt .= 'height: '.$pr[2].'; ';
                            break;
                        case 'align':
                            if($pr[2]!='center')
                                $csstxt .= 'float: '.$pr[2].'; ';
                            break;
                        default:
                            $paramtxt.=' '.$pr[1].'="'.$pr[2].'"';
                    }
                }
            }
            $paramtxt .= ($csstxt!=''?' style="'.$csstxt.'"':'');
            //return '<a href="'.$this->prefix.'/'.self::encodeURI($category[0]).'" title="'.htmlspecialchars($category[0]).'"><img src="https://namu.wiki/file/'.self::encodeURI($category[0]).'"'.$paramtxt.'></a>';
        }else {
            // [[:분류:ㅁ]]
            if(self::startsWith($href[0], ':')) {
                $href[0] = substr($href[0], 1);
                $c=1;
            }

            //+ 링크에 #이 포함되는 경우
            if(strpos($href[0], '#') !== false && empty($href[1])){
                $display_txt = explode('#', $href[0])[0];
            }else{
                $display_txt = (!empty($href[1])?$this->formatParser($href[1]):$href[0]);
            }
            
            //+ [[../../]] ㄱ/ㄴ/ㄷ > ㄱ
            if(self::startsWith($href[0], '../')){
                $lv = count(explode('../', $href[0])) - 1;
                for($i=0; $i<$lv; ++$i){
                    $ttlvl = explode('/', $this->title);
                    array_pop($ttlvl);
                    $href[0] = implode('/', $ttlvl);
                }
            }
            //+ [[/a/b]] ㄱ > ㄱ/ㄴ/ㄷ
            if(self::startsWith($href[0], '/')){
                $href[0] = $this->title.$href[0];
            }

            $targetUrl = $this->uriset['wiki'].self::encodeURI($href[0]);
            if($this->wapRender && !empty($href[1])){
                $title = $href[0];
                if($this->pageExists($title) === false){
                    $class = 'l-it';
                }
            }
            if(empty($c))
                array_push($this->links, array('target'=>$href[0], 'type'=>'link'));
        }
        return '<a href="'.$targetUrl.'"'.(!empty($title)?' title="'.$title.'"':'').(!empty($class)?' class="'.$class.'"':'').(!empty($target)?' target="'.$target.'"':'').'>'.$display_txt.'</a>';
    }

    // 대괄호 문법
    private function macroProcessor($text, $type) {
        $macroName = strtolower($text);
        if(!empty($this->macro_processors[$macroName]))
            return $this->macro_processors[$macroName]();
        switch($macroName) {
            case 'br':
                // [br]
                return '<br>';
            case 'date':
            case 'datetime':
                return date('Y-m-d H:i:s');
            case '목차':
            case 'tableofcontents':
                // 목차
                return $this->printToc();
            case '각주':
            case 'footnote':
                // 각주모음
                return $this->printFootnote();
            //+ clearfix
            case 'clearfix':
                return '<div style="clear:both;"></div>';
            default:
                if(self::startsWithi($text, 'include') && preg_match('/^include\((.+)\)$/i', $text, $include) && $include = $include[1] && $this->inThread !== false) {
                    // include 문법
                    if($this->included)
                        return ' ';

                    $include = explode(',', $include);
                    array_push($this->links, array('target'=>$include[0], 'type'=>'include'));
                    /*if(($page = $this->WikiPage->getPage($include[0])) && !empty($page->text)) {
                        foreach($include as $var) {
                            $var = explode('=', ltrim($var));
                            if(empty($var[1]))
                                $var[1]='';
                            $page->text = str_replace('@'.$var[0].'@', $var[1], $page->text);
                            // 틀 변수
                        }
                        $child = new NamuMark($page);
                        $child->prefix = $this->prefix;
                        $child->imageAsLink = $this->imageAsLink;
                        $child->wapRender = $this->wapRender;
                        $child->included = true;
                        return $child->toHtml();
                    }*/
                    return ' ';
                }
                elseif(preg_match('/^(youtube|nicovideo|kakaotv)\((.+)\)$/i', $text, $include) && $include = $include[2] && $this->inThread !== false) {
                    // 동영상
                    $include = explode(',', $include);
                    $var = array();
                    foreach($include as $v) {
                        $v = explode('=', $v);
                        if(empty($v[1]))
                            $v[1]='';
                        $var[$v[0]] = $v[1];
                    }
                    return '<iframe width="'.(!empty($var['width'])?$var['width']:'640').'" height="'.(!empty($var['height'])?$var['height']:'360').'" src="//'.$this->videoURL[$include[1]].$include[0].'" frameborder="0" allowfullscreen></iframe>';
                }
                elseif(preg_match('/^age\(([0-9]{4))-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\)$/i', $text, $include) && $this->inThread !== false) {
                    // 연령
                    $age = (date("md", date("U", mktime(0, 0, 0, $include[3], $include[2], $include[1]))) > date("md")
                        ? ((date("Y") - $include[1]) - 1)
                        : (date("Y") - $include[1]));
                    return $age;
                }
                elseif(preg_match('/^anchor\((.+)\)$/i', $text, $include) && $include = $include[1]) {
                    // 앵커
                    return '<a name="'.$include.'"></a>';
                }
                elseif(preg_match('/^dday\((.+)\)$/i', $text, $include) && $include = $include[1] && $this->inThread !== false) {
                    // D-DAY
                    $nDate = date("Y-m-d", time());
                    if(strtotime($nDate)==strtotime($include)){
                        return " 0";
                    }
                    return intval((strtotime($nDate)-strtotime($include)) / 86400);
                }
                elseif(preg_match('/^ruby\((.+)\)$/i', $text, $include) && $this->inThread !== false) {
                    $ruby = explode(',', $include[1]);
                    foreach(array_slice($ruby, 1) as $a){
                        $split = explode('=', $a);
                        if($split[0] == 'ruby'){
                            $rb = $split[1];
                        }elseif($split[0] == 'color' && in_array($split[1], $this->cssColors)){
                            $color = $split[1];
                        }
                    }
                    if(isset($color)){
                        $rb = '<span style="color:'.$color.'">'.$rb.'</span>';
                    }
                    if(strlen($rb) > 0){
                        return '<ruby>'.$ruby[0].'<rp>(</rp><rt>'.$rb.'</rt><rp>)</rp></ruby>';
                    }else{
                        return ' ';
                    }
                    
                }
                elseif(preg_match('/^pagecount\((.+)\)$/i', $text, $include) && $include = $include[1] && $this->inThread !== false) {
                    
                }
                elseif(preg_match('/^\*([^ ]*)([ ].+)?$/', $text, $note)) {
                    // 각주
                    $notetext = !empty($note[2])?$this->blockParser($note[2]):'';
                    $id = $this->fnInsert($this->fn, $notetext, $note[1]);
                    $preview = $notetext;
                    $preview2 = strip_tags($preview, '<img>');
                    $preview = strip_tags($preview);
                    $preview = str_replace('"', '\\"', $preview);
                    return '<a class="wiki-fn-content" href="#fn-'.rawurlencode($id).'"><span class="target" id="rfn-'.htmlspecialchars($id).'"></span>['.($note[1]?$note[1]:$id).']</a>';
                }
        }
        return '['.$text.']';
    }

    // TheWiki Parser 일부
    private function textProcessor($otext, $type) {
        if($type !== '{{{')
            $text = $this->formatParser($otext);
        else
            $text = $otext;
            switch ($type) {
            case "'''":
                // 볼드체
                return '<strong>'.$text.'</strong>';
            case "''":
                // 기울임꼴
                return '<em>'.$text.'</em>';
            case '--':
            case '~~':
                // 취소선
                // + 수평선 적용 안 되는 오류 수정
                if(@$this->strikeLine){
                    $text = '';
                }
                return '<del>'.$text.'</del>';
                    // no break
            case '__':
                // 목차 / 밑줄
                        return '<u>'.$text.'</u>';
                    // no break
            case '^^':
                // 위첨자
                            return '<sup>'.$text.'</sup>';
            case ',,':
                // 아래첨자
                        return '<sub>'.$text.'</sub>';
            case '{{{':
                // HTML
                if(self::startsWith($text, '#!html') && $this->inThread !== false) {
                    $html = substr($text, 6);
                    $html = ltrim($html);
                    $html = htmlspecialchars_decode($html);
                    $html = self::inlineHtml($html);
                    return $html;
                }
                elseif(preg_match('/^#(?:([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})|([A-Za-z]+)) (.*)$/', $text, $color)) {
                    if(empty($color[1]) && empty($color[2]))
                        return $text;
                    return '<span style="color: '.(empty($color[1])?$color[2]:'#'.$color[1]).'">'.$this->formatParser($color[3]).'</span>';
                }
                elseif(preg_match('/^\+([1-5]) (.*)$/', $text, $size)) {
                    return '<span class="wiki-size size-'.$size[1].'">'.$this->formatParser($size[2]).'</span>';
                }
                    // 문법 이스케이프
                return '<code class="wiki-escape">' . $text . '</code>';
            }
            return $type.$text.$type;
    }

    // 각주 삽입
    private function fnInsert(&$arr, &$text, $id = null) {
        $arr_cnt = self::count($arr);
        if(empty($id)) {
            $multi = false;
            $id = ++$this->fn_cnt;
        }
        else {
            $multi = true;
            for($i=0;$i<$arr_cnt;$i++) {
                if($arr[$i]['id']==$id) {
                    $arr[$i]['count']++;
                    if(!empty(trim($text)))
                        $arr[$i]['text'] = $text;
                    else
                        $text = $arr[$i]['text'];
                    return $id.'-'.$arr[$i]['count'];
                }
            }
        }
        $arr[] = array('id' => $id, 'text' => $text, 'count' => 1);
        return $multi?$id.'-1':$id;
    }

    // 페이지 하단 각주 목록
    private function printFootnote() {
        if(self::count($this->fn)==0)
            return '';

        $result = $this->wapRender?'<hr>':'<hr><ol class="fn">';
        foreach($this->fn as $k => $fn) {
            $result .= $this->wapRender?'<p>':'<span>';
            if($fn['count']>1) {
                $result .= '['.$fn['id'].'] ';
                for($i=0;$i<$fn['count'];$i++) {
                    if(isset($this->lastfnid))
                        $i += $this->lastfnid;
                    $result .= '<a id="fn-'.htmlspecialchars($fn['id']).'-'.($i+1).'" href="#rfn-'.rawurlencode($fn['id']).'-'.($i+1).'">'.chr(ord('A') + $i).'</a> ';
                    $this->lastfnid = $i+1;
                }
            }
            else {
                if(isset($this->lastfnid) && is_int($fn['id'])){
                    $fn['id'] += $this->lastfnid;
                    $this->lastfnid = $fn['id'];
                }
                $result .= '<a id="fn-'.htmlspecialchars($fn['id']).'" href="#rfn-'.$fn['id'].'">['.$fn['id'].']</a> ';
                
            }
            $result .= $this->blockParser($fn['text'])
                                .($this->wapRender?'</p>':'</span><br>');
        }
        $result .= $this->wapRender?'':'</ol>';
        //$this->fn = array();
        return $result;
    }

    // 목차 삽입
    private function tocInsert(&$arr, $text, $level, $path = '') {
        if(empty($arr[0])) {
            $arr[0] = array('name' => $text, 'level' => $level, 'childNodes' => array());
            return $path.'1';
        }
        $last = self::count($arr)-1;
        $readableId = $last+1;
        if($arr[0]['level'] >= $level) {
            $arr[] = array('name' => $text, 'level' => $level, 'childNodes' => array());
            return $path.($readableId+1);
        }

        return $this->tocInsert($arr[$last]['childNodes'], $text, $level, $path.$readableId.'.');
    }

    private function hParse(&$text) {
        // 행 분리
        $lines = explode("\n", $text);
        $result = '';
        foreach($lines as $line) {
            $matched = false;
            foreach($this->h_tag as $tag_ar) {
                $tag = $tag_ar[0];
                $level = $tag_ar[1];
                if(!empty($tag) && preg_match($tag, $line, $match)) {
                    $this->tocInsert($this->toc, $this->blockParser($match[1]), $level);
                    $matched = true;
                    break;
                }
            }
        }

        return $result;
    }

    // HTML 목차 출력
    private function printToc(&$arr = null, $level = -1, $path = '') {
        if($level == -1) {
            $bak = $this->toc;
            $this->toc = array();
            $this->hParse($this->WikiPage->text);
            $result = ''
                .'<div id="_toc">'
                    //.($this->wapRender!==false?'<h2>목차</h2>':'')
                    .$this->printToc($this->toc, 0)
                .'</div>'
                .'';
            $this->toc = $bak;
            return $result;
        }

        if(empty($arr[0]))
            return '';

        // + 목차에 앵커 들어가는거 수정
        $result  = '<div class="_toc_ln">';
        foreach($arr as $i => $item) {
            $readableId = $i+1;
            $result .= '<span _toc-item><a href="#s-'.$path.$readableId.'">'.$path.$readableId.'</a>. '
                            .preg_replace('/\[anchor\((.*)\)\]/', '', $item['name']).'</span>'
                            .$this->printToc($item['childNodes'], $level+1, $path.$readableId.'.')
                            .'';
        }
        $result .= '</div>';
        return $result;
    }

    private static function getChar($string, $pointer){
        if(!isset($string[$pointer])) return false;
        $char = ord($string[$pointer]);
        if($char < 128){
            return $string[$pointer];
        }else{
            if($char < 224){
                $bytes = 2;
            }elseif($char < 240){
                $bytes = 3;
            }elseif($char < 248){
                $bytes = 4;
            }elseif($char == 252){
                $bytes = 5;
            }else{
                $bytes = 6;
            }
            $str = substr($string, $pointer, $bytes);
            return $str;
        }
    }

    private static function nextChar($string, &$pointer){
        if(!isset($string[$pointer])) return false;
        $char = ord($string[$pointer]);
        if($char < 128){
            return $string[$pointer++];
        }else{
            if($char < 224){
                $bytes = 2;
            }elseif($char < 240){
                $bytes = 3;
            }elseif($char < 248){
                $bytes = 4;
            }elseif($char == 252){
                $bytes = 5;
            }else{
                $bytes = 6;
            }
            $str = substr($string, $pointer, $bytes);
            $pointer += $bytes;
            return $str;
        }
    }

    private static function count($var){
        if(!is_array($var) && !is_countable($var))
            return false;
        else
            return count($var);
    }

    private static function startsWith($haystack, $needle, $offset = 0) {
        $len = strlen($needle);
        if(($offset+$len)>strlen($haystack))
            return false;
        return $needle == substr($haystack, $offset, $len);
    }

    private static function startsWithi($haystack, $needle, $offset = 0) {
        $len = strlen($needle);
        if(($offset+$len)>strlen($haystack))
            return false;
        return strtolower($needle) == strtolower(substr($haystack, $offset, $len));
    }

    private static function seekEndOfLine($text, $offset=0) {
        return self::seekStr($text, "\n", $offset);
    }

    private static function seekStr($text, $str, $offset=0) {
        if($offset >= strlen($text) || $offset < 0)
            return strlen($text);
        return ($r=strpos($text, $str, $offset))===false?strlen($text):$r;
    }

    // HTML 문법
    private static function inlineHtml($html) {
        $html = str_replace("\n", '', $html);
        $html = preg_replace('/<\/?(?:object|param)[^>]*>/', '', $html);
        $html = preg_replace('/<embed([^>]+)>/', '<iframe$1 frameborder="0"></iframe>', $html);
        $html = preg_replace('/(<img[^>]*[ ]+src=[\'\"]?)(https?\:[^\'\"\s]+)([\'\"]?)/', '$1$2$3', $html);
        return $html;
    }

    private static function encodeURI($str) {
        return str_replace(array('%3A', '%2F', '%23', '%28', '%29'), array(':', '/', '#', '(', ')'), rawurlencode($str));
    }
}

class HTMLElement {
    public $tagName, $innerHTML, $attributes;
    function __construct($tagname) {
        $this->tagName = $tagname;
        $this->innerHTML = null;
        $this->attributes = array();
        $this->style = array();
    }

    public function toString() {
        $style = $attr = '';
        if(!empty($this->style)) {
            foreach($this->style as $key => $value) {
                $value = str_replace('\\', '\\\\', $value);
                $value = str_replace('"', '\\"', $value);
                $style.=$key.':'.$value.';';
            }
            $this->attributes['style'] = substr($style, 0, -1);
        }
        if(!empty($this->attributes)) {
            foreach($this->attributes as $key => $value) {
                $value = str_replace('\\', '\\\\', $value);
                $value = str_replace('"', '\\"', $value);
                $attr.=' '.$key.'="'.$value.'"';
            }
        }
        return '<'.$this->tagName.$attr.'>'.$this->innerHTML.'</'.$this->tagName.'>';
    }
}
