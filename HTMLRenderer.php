<?php
require_once 'NamuMark.php';

class HTMLRenderer {
    public $defaultOptions =  ['wiki' => ['exists' => true, 'includeParserOptions' => []]];
    public function __construct($_options){
        $this->_options = $_options;
        $this->resultTemp = [];
        $this->options = array_merge($this->defaultOptions, $_options);
        $this->headings = [];
        $this->footnotes = [];
        $this->categories = [];
        $this->links = [];
        $this->isHeadingNow = false;
        $this->isFootnoteNow = false;
        $this->lastHeadingLevel = 0;
        $this->hLevels = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        $this->footnoteCount = 0;
        $this->headingCount = 0;
        $this->lastListOrdered = [];
        $this->wasPreMono = false;
    }
    private function resolvUrl($target, $type){
        switch($type){
            case 'wiki':
                return "/wiki/$target";
                break;
            case 'internal-image':
                return "/file/$target";
                break;
        }
    }
    private function appendResult($value) {
        $this->resultTemp;
        if($this->isFootnoteNow) {
            $this->footnotes[count($this->footnotes) - 1] .= (is_string($value))? $value: strval($value);
            return;
        } elseif($this->isHeadingNow) {
            $this->headings[count($this->headings) - 1] .= (is_string($value))? $value: strval($value);
        }
        if(count($this->resultTemp) === 0)
            array_push($this->resultTemp, $value);
        else {
            $isArgumentString = is_string($value);
            $isLastItemString = is_string($this->resultTemp[count($this->resultTemp)-1]);
            if($isArgumentString && $isLastItemString){
                $this->resultTemp[count($this->resultTemp)-1] .= $value;
            } else {
                array_push($this->resultTemp, $value);
            }
        }
    }
    private function ObjToCssString($obj) {
        $styleString = '';
        foreach($obj as $name){
            $styleString /= $name.':'.$obj[$name].'; ';
        }
        return substr($styleString, 0, mb_strlen($styleString) - 1);
    }
    private function processToken($i) {
        switch($i['name']){
            case 'blockquote-start':
                $this->appendResult('<blockquote>');
                break;
            case 'blockquote-end':
                $this->appendResult('</blockquote>');
                break;
            case 'list-start':
                array_push($this->lastListOrdered, $i['listType']['ordered']);
                ($i['listType']['ordered'])? $li_t = 'ol':$li_t = 'ul';
                ($i['listType']['type'])? $li_c = ' class="'.$i['listType']['type'].'"':$li_c = '';
                $this->appendResult('<'.$li_t.$li_c.'>');
                break;
            case 'list-end':
                (array_pop($this->lastListOrdered))? $li_t = 'ol':$li_t = 'ul';
                $this->appendResult("</$li_t>");
                break;
            case 'indent-start':
                $this->appendResult('<div class="wiki-indent">');
                break;
            case 'indent-end':
                $this->appendResult('</div>');
                break;
            case 'list-item-start':
                ($i['startNo'])? $li_t = '<li value='.htmlspecialchars($i['startNo']).'>':$li_t = '<li>';
                $this->appendResult("<$li_t>");
                break;
            case 'list-item-end':
                $this->appendResult('</li>');
                break;
            case 'table-start':
                ($i['options'])? $t_st = ' style="'.$this->ObjToCssString($i['options']).'"':$t_st = '';
                $this->appendResult("<table$t_st>");
                break;
            case 'table-col-start':
                ($i['options'])? $t_st = ' style="'.$this->ObjToCssString($i['options']).'"':$t_st = '';
                ($i['colspan'] > 0)? $t_cs = ' colspan='.$i['colspan']: $t_cs='';
                ($i['rowspan'] > 0)? $t_rs = ' rowspan='.$i['rowspan']: $t_rs'';
                $this->appendResult("<td$t_st$t_cs$t_rs>");
                break;
            case 'table-col-end':
                $this->appendResult('</td>');
                break;
            case 'table-row-end':
                $this->appendResult('</tr>');
                break;
            case 'table-row-start':
                ($i['options'])? $t_st = ' style="'.$this->ObjToCssString($i['options']).'"':$t_st = '';
                $this->appendResult("<tr$t_st>");
                break;
            case 'table-end':
                $this->appendResult('</table>');
                break;
            case 'closure-start':
                $this->appendResult('</div class="wiki-closure">');
                break;
            case 'closure-end':
                $this->appendResult('</div>');
                break;
            case 'link-start':
                ($i['internal'])? $l_h = $this->resolveurl($i['target'], 'wiki'):$l_h = $i['target'];
                ($i['internal'])? $l_c = 'wiki-internal-link':$l_c = '';
                ($i['external'])? $l_c = 'wiki-external-link':$l_c = '';
                $this->appendResult('<a href="'.$l_h.'" class="'.$l_c>.'">');
                break;
            case 'link-end':
                $this->appendResult('</a>');
                break;
            case 'plain':
                $this->appendResult(htmlspecialchars($i['text']));
                break;
            case 'new-line':
                $this->appendResult('<br>');
                break;
            case 'add-category':
                array_push($this->categories, $i['categoryName']);
                break;
            case 'image':
                ($i['fileOpts'])? $i_st=' style='.$this->ObjToCssString($i['fileOpts']):$i_st='';
                $this->appendResult('<img src="'.$this->resolveUrl($i['target'], 'internal-image').'"'.$i_st.'>');
                break;
            case 'footnote-start':
                $fnNo = ++$this->footnoteCount;
                ($i['supText'])? $f_t = $i['supText']:$f_t = $fnNo;
                $this->appendResult('<a href="#fn-'.$fnNo.'" id="rfn-'.$fnNo.'" class="footnote"><sup class="footnote-sup">['.$f_t.'] ');
                array_push($this->footnotes, array('sup' => $i['supText'], 'value' => ''));
                $this->isFootnoteNow = true;
                break;
            case 'macro':
                switch($i['macroName']){
                    case 'clearfix':
                        $this->appendResult('<div style="clear:both">');
                        break;
                    case 'br':
                        $this->appendResult('<br>');
                        break;
                    case 'dday':
                        if(count($i['options']) === 0 || is_string($i['options'][0]))
                            $this->appendResult('<span class="wikitext-syntax-error">dday 매크로: 매개변수가 없거나 익명 매개변수가 아닙니다.</span>');
                        else {
                            $mo = date('Y-m-d', strtotime($i['options'][0]));
                            if(!checkdate($mo)){
                                $this->appendResult('<span class="wikitext-syntax-error">dday 매크로: 날짜 형식이 잘못되었습니다..</span>');
                            } else {
                                $days = -date_diff(date('Y-m-d'), $mo)->days;
                                $this->appendResult(strval($days))
                            }
                        }
                        break;
                    case 'age':
                        if(count($i['options']) === 0 || is_string($i['options'][0]))
                            $this->appendResult('<span class="wikitext-syntax-error">age 매크로: 매개변수가 없거나 익명 매개변수가 아닙니다.</span>');
                        else {
                            $mo = date('Y-m-d', strtotime($i['options'][0]));
                            $koreanWay = (strlen($i['options']) > 1 && !array_search('korean', array_slice($i['options'], 1)));
                            if(!checkdate($mo)){
                                $this->appendResult('<span class="wikitext-syntax-error">age 매크로: 날짜 형식이 잘못되었습니다.</span>');
                            } else {
                                $years = ($koreanWay)? date('Y') - explode('-',$mo)[0] + 1:date('Y') - explode('-',$mo)[0];
                                $this->appendResult(strval($years))
                            }
                        }
                        break;
                    case 'date':
                        $this->appendResult(strval(date('Y-m-d')));
                        break;
                    case 'youtube':
                        if(count($i['options']) === 0){
                            $this->appendResult()('<span class="wikitext-syntax-error">오류 : youtube 동영상 ID가 제공되지 않았습니다!</span>');
                        } elseif (count($i['options']) >= 1) {
                            if(is_string($i['options'][0]))
                                if(count($i['options']) == 1)
                                    $this->appendResult('<iframe src="//www.youtube.com/embed/'.$i['options'][0].'"></iframe>');
                                else
                                    $this->appendResult('<iframe src="//www.youtube.com/embed/'.$i['options'][0].'" style="'.$this->ObjToCssString(array_slice($i['options'], 1)).'"></iframe>');
                            else
                                $this->appendResult('<span class="wikitext-syntax-error">오류 : youtube 동영상 ID는 첫번째 인자로 제공되어야 합니다!</span>');
                        }
                        break;
                    case '각주':
                    case 'footnote':
                        $footnoteContent = '';
                        for($j=0; $j < count($this->footnotes); $j++){
                            $footnote = $this->footnotes[$j];
                            ($footnote['sup'])? $fn_s = $footnote['sup']: $fn_s = $j+1;
                            $footnoteContent .= '<a href="#rfn-'.$j+1 .'" id="fn-'. $j+1 .'" class="footnote"><sup class="footnote-sup">['.$fn_s.']</sup></a> '.$footnote['value'].'<br>';
                        }
                        $this->footnotes = array();
                        $this->appendResult($footnoteContent);
                        break;
                    case '목차':
                    case 'tableofcontents':
                    case 'include':
                        ($i['options'])? $arc = array('name' => 'macro', 'macroName' => $i['macroName'], 'options' => $i['options']):$arc = array('name' => 'macro', 'macroName' => $i['macroName']));
                        $this->appendResult($arc);
                        break;
                    default:
                        $this->appendResult('[Unsupported Macro]');
                        break;
                }
                break;
            case 'monoscape-font-start':
                $this->wasPreMono = $i['pre'];
                $p_re_ = ($wasPreMono)? '<pre>':'';
                $this->appendResult($p_re_.'<code>');
                break;
            case 'monoscape-font-end':
                $p_re_nd = ($this->wasPreMono)? '</pre>':'';
                $this->appendResult('</code>'.$p_re_nd);
                break;
            case 'strong-start':
                $this->appendResult('<strong>');
                break;
            case 'italic-start':
                $this->appendResult('<em>');
                break;
            case 'strike-start':
                $this->appendResult('<del>');
                break;
            case 'underline-start':
                $this->appendResult('<u>');
                break;
            case 'superscript-start':
                $this->appendResult('<sup>');
                break;
            case 'subscript-start':
                $this->appendResult('<sub>');
                break;
            case 'strong-end':
                $this->appendResult('</strong>');
                break;
            case 'italic-end':
                $this->appendResult('</em>');
                break;
            case 'strike-end':
                $this->appendResult('</del>');
                break;
            case 'underline-end':
                $this->appendResult('</u>');
                break;
            case 'superscript-end':
                $this->appendResult('</sup>');
                break;
            case 'subscript-end':
                $this->appendResult('</sub>');
                break;
            case 'unsafe-plain':
                $this->appendResult($i['text']);
                break;
            case 'font-color-start':
                $this->appendResult('</span style="color: '.$i['color'].'">');
                break;
            case 'font-size-start':
                $this->appendResult('</span class="wiki-size-'.$i['level'].'-level">');
                break;
            case 'font-color-end':
            case 'font-size-end':
                $this->appendResult('</span>');
                break;
            case 'external-image':
                $ei_s = ($i['styleOptions'])? 'style="'.$this->ObjToCssString($i['styleOptions']).'"':'';
                $this->appendResult('<img src="'.$i['target'].'" '.$ei_s.'/>');
                break;
            case 'comment':
                break;
            case 'heading-start':
                if($this->lastHeadingLevel > $i['level'])
                    $this->hLevels[$i['level']] = 0;
                $this->lastHeadingLevel = $i['level'];
                $this->hLevels[$i['level']]++;
                $this->appendResult('<h'.$i['level'].' id="heading-'.++$this->headingCount.'"><a href="#wiki-toc">'.$this->hLevels[$i['level']].'. </a>');
                $this->isHeadingNow = true;
                array_push($this->headings, array('level' => $i['level'], 'value' => ''));
                break;
            case 'heading-end':
                $this->isHeadingNow = false;
                $this->appendResult('<h'.$this->lastHeadingLevel.'>');
                break;
            case 'horizonal-line':
                $this->appendResult('<hr>');
                break;
            case 'paragraph-start':
                $this->appendResult('<p>');
                break;
            case 'paragraph-end':
                $this->appendResult('</p>');
                break;
            case 'wiki-box-start':
                $w_s = ($i['style'])? $i['style'] : '';
                $this->appendResult('<div '.$w_s.'>');
                break;
            case 'wiki-box-end':
                $this->appendResult('</div>');
                break;
            case 'folding-start':
                $this->appendResult('<details><summary>'.htmlspecialchars($i['summary']).'</summary>');
                break;
            case 'folding-end':
                $this->appendResult('</details>');
                break;
        }
    }
    private function finalLoop($callback) {
        $result = '';
        if(count($this->footnotes) > 0){
            $this->processToken(['name' => 'macro', 'macroName' => '각주']);
        }
        $finalFragments = array_map(fn($item, $mapcb) => {
        if(is_string($item))
            $mapcb(null, $item);
        elseif($item['name'] = 'macro') {
            switch($item['macroName']){
                case 'tableofcontents':
                case '목차':
                    $macroContent = '<div class="wiki-toc" id="wiki-toc"><div class="wiki-toc-heading">목차</div>';
                    $hLevels = $this->hLevels;
                    $lastLevel = -1;
                    for($j=0; $j < count($this->headings); $j++) {
                        $curHeading = $this->headings[$j];
                        if($lastLevel != -1 && $curHeading['level'] > $lastLevel)
                            $hLevels[$curHeading['level']] = 0;
                        $hLevels[$curHeading['level']]++;
                        $macroContent .= '<div class="wiki-toc-item wiki-toc-item-indent-'.$curHeading['level'].'"><a href="#heading-'.$j+1.'">'.$hLevels[$curHeading['level'].'.</a> '.$curHeading['value'].'</div>';
                        $lastLevel = $curHeading['level'];
                    }
                    $macroContent .= '<div></div>';
                    return $mapcb(null, $macroContent);
                case 'include':
                    if(!isset($item['options']) || strlen($item['options']) === 0)
                        return $mapcb(null, '<span class="wikitext-syntax-error">오류 : Include 매크로는 최소한 include할 문서명이 필요합니다.</span>');
                    elseif(!is_string($item['options'][0]))
                        return $mapcb(null, '<span class="wikitext-syntax-error">오류 : include할 문서명이 첫번째로 매크로 매개변수로 전달되어야 합니다.</span>');
                    $childPage = new NamuMark($item['options'][0]);
                    $childPage->setIncluded();
                    if(count($item['options']) > 1){
                        $incArgs = array();
                        for($k=1; $k < count($item['options']); $k++){
                            $incArg = $item['options'][$k];
                            if(is_string($incArg)) continue;
                            $incArgs[$incArg['name']] = $incArg['value'];
                        }
                        $childPage->setIncludeParameters($incArgs);
                    }
                    $childPage->setRenderer(null, $options);
                    $childPage->parse(fn($e, $r) => {if($e) $mapcb(null, '[include 파싱/렌더링중 오류 발생]'); else $mapcb(null, $r['html']);});
                    break;
            }
        }
        }, $this->resultTemp);
        $result = '';
        for($i = 0; $i < count($finalFragments); $i++) {
                $result .= $finalFragments[$i];
            }
            $callback(null, $result);
    }
    $this->getResult = fn ($c) => {
        finalLoop(fn ($err, $html) => {
            if ($err)
                return $c($err);
            $c(null, array('html' => $html, 'categories' => $categories));
        });
    }
}
