<?php
require_once 'NamuMark.php';

// callback: (err,result) => { if(err) console.log('ERROR') let html, categories = result; console.log(complete0
function gr_cb($err, $result){
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
}
