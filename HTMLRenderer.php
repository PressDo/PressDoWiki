<?php
require_once 'NamuMark.php';

// callback: (err,result) => { if(err) console.log('ERROR') let html, categories = result; console.log(complete0
function getresult_callback($err, $result){
    if($err) callback($err);
    else callback(null, $result);
}
// Original Code: getResult(gr_cb())

class HTMLRenderer {
    public $defaultOptions = array(
        'wiki' => array(
            'exists' => true,
            'includeParserOptions' => array()
        )
    );
    public function __construct($_options){
        $this->_options = $_options;
        $this->resultTemp = array();
        $this->options = array_merge($this->defaultOptions, $_options);
        $this->headings = array();
        $this->footnotes = array();
        $this->categories = array();
        $this->links = array();
        $this->isHeadingNow = false;
        $this->isFootnoteNow = false;
        $this->lastHeadingLevel = 0;
        $this->hLevels = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
        $this->footnoteCount = 0;
        $this->headingCount = 0;
        $this->lastListOrdered = array();
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
        $resultTemp = $this->resultTemp;
        if($this->isFootnoteNow) {
            $this->footnotes[count($this->footnotes) - 1] .= (is_string($value))? $value: strval($value);
            return;
        } elseif($this->isHeadingNow) {
            $this->headings[count($this->headings) - 1] .= (is_string($value))? $value: strval($value);
        }
        if(count($resultTemp) === 0)
            array_push($resultTemp, $value);
        else {
            $isArgumentString = is_string($value);
            $isLastItemString = is_string($resultTemp[count($resultTemp)-1]);
            if($isArgumentString && $isLastItemString){
                $resultTemp[count($resultTemp)-1] .= $value;
            } else {
                array_push($resultTemp, $value);
            }
            $this->resultTemp = $resultTemp;
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
                appendResult('<blockquote>');
                break;
            # 계속 추가
        }
    }
    private function finalLoop() {
        $result = '';
        if(count($this->footnotes) > 0){
            $this->processToken(array('name' => 'macro', 'macroName' => '각주'));
        }
        if(is_string($item))
            mapcb(null, $item);
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
                    return mapcb(null, $macroContent);
                case 'include':
                    if(!isset($item['options']) || strlen($item['options']) === 0)
                        return mapcb(null, '<span class="wikitext-syntax-error">오류 : Include 매크로는 최소한 include할 문서명이 필요합니다.</span>');
                    elseif(!is_string($item['options'][0]))
                        return mapcb(null, '<span class="wikitext-syntax-error">오류 : include할 문서명이 첫번째로 매크로 매개변수로 전달되어야 합니다.</span>');
                    $childPage = new NamuMark($item['options']);
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
                    $childPage->parse;
                    break;
            }
        }
    }
    function getResult() {
        finalLoop();
    }
}
