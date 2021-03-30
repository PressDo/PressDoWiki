<?php
require_once 'HTMLRenderer.php';


$redirectPattern = '/^#(?:redirect|넘겨주기) (.+)$/im';
function seekEOL($text, $offset = 0){
	return (strpos($text, '\n', $offset) == -1 )? mb_strlen($text) : strpos($text, '\n', $offset);
}

class NamuMark{
	public function __construct($content, $_options=array()){
		global $wikitext;
		$defaultOptions = array(
			'wiki' => array(
				'read' => $content
			),
			'allowedExternalImageExts' => $_options,
			'included' => false,
			'includeParameters' => array(),
			'macroNames' => array('br', 'date', '목차', 'tableofcontents', '각주', 'footnote', 'toc', 'youtube', 'nicovideo', 'kakaotv', 'include', 'age', 'dday')
		);
		$options = $defaultOptions;
		$wikitext = $options['wiki']['read'];
		$rendererOptions = null;
		$renderer = null;
	}		
	private static function doParse(){
		global $wikitext;
		$multiBrackets = array(
			'open' => '{{{',
			'close' => '}}}',
			'multiline' => true,
			'processor' => 'renderProcessor'
		);
		$renderer = ($rendererOptions)? new HTMLReader($rendererOptions):new HTMLReader();
		$line = '';
		$now = '';
		$tokens = array();
		if($wikitext === null)
		    return array(array('name' => 'error', 'type' => 'notfound'));
		if(str_starts_with($wikitext, '#') && preg_match($redirectPattern, $wikitext, $r_match) && strpos($wikitext, $r_match[0]) === 0)
	        return array(array('name' => 'redirect', 'target' => $r_match[1]));
		for($i=0;$i < mb_strlen($wikitext); $i++){
			$temp = array('pos' => $i);
			$now = substr($wikitext,$i,1);

			// func[0] = Gijon gap, func[1]: v => i = v
			if($line == '' && $now == ' ' && $l_i = listParser($wikitext, $i) && $temp = $l_i[0] && $i = $l_i[1]){
				$tokens = array_push($tokens, $temp);
				$line = '';
				$now = '';
				continue;
			}
			if($line == '' && str_starts_with(substr($wikitext, $i), '|') && $t_i = tableParser($wikitext, $i) && $temp = $t_i[0] && $i = $t_i[1]){
				$tokens = array_push($tokens, $temp);
				$line = '';
				$now = '';
				continue;
			}
			if($line == '' && str_starts_with(substr($wikitext, $i), '>') && $q_i = blockquoteParser($wikitext, $i) && $temp = $q_i[0] && $i = $q_i[1]){
				$tokens = array_push($tokens, $temp);
				$line = '';
				$now = '';
				continue;
			}
			foreach($multiBrackets as $bracket){
				// Callproc moved into func
				if(str_starts_with(substr($wikitext, $i), $bracket['open']) && $b_i = bracketParser($wikitext, $i) && $temp = $b_i[0] && $i = $b_i[1]){
					$tokens = array_push($tokens, array(array('name' => 'wikitext', 'treatAsLine' => true, 'text' => $line)),$temp);
					$line = '';
					$now = '';
					break;
				}
			}
			if($now === '\n'){
				$tokens = array_push($tokens, array(array('name' => 'wikitext', 'treatAsLine' => true, 'text' => $line)));
			    $line = '';
			}
			else
			    $line .= $now;
		}
		if(mb_strlen($line) != 0)
		    $tokens = array_push($tokens,array(array('name' => 'wikitext', 'treatAsLine' => true, 'text' => $line)));
		function processTokens($newarr){
			if(!is_array($newarr)) $newarr = array();
			for($i=0;$i < count($newarr); $i++){
				$v = $newarr[$i];
				if(is_array($v))
				    processTokens($v);
				elseif($v['name'] !== 'wikitext')
				    $renderer->processToken($v);
				elseif($v['parseFormat'] || $v['treatAsBlock'])
				    processTokens(blockParser($v['text']));
				elseif($v['treatAsLine'])
				    processTokens(lineParser($v['text']));
			}
		}
		processTokens($tokens);
		$renderer->getResult();
	}

	// do not forget to add callprocessor func in parser
	function blockParser($line){
		$result = array();
		$singleBrackets = array(
			array(
				'open' => '{{{',
				'close' => '}}}',
				'multiline' => false,
				'processor' => 'textProcessor'
			),
			array(
				'open' => '{{|',
				'close' => '|}}',
				'multiline' => false,
				'processor' => 'closureProcessor'
			),
			array(
				'open' => '[[',
				'close' => ']]',
				'multiline' => false,
				'processor' => 'linkProcessor'
			),
			array(
				'open' => '[',
				'close' => ']',
				'multiline' => false,
				'processor' => 'macroProcessor'
			),
			array(
				'open' => '@',
				'close' => '@',
				'multiline' => false,
				'processor' => 'textProcessor'
			)
		);
		$plainTemp = '';
		for($j=0;$j<mb_strlen($line);$j++){
			$extImgPattern = '/(https?:\\/\\/[^ \\n]+(?:\\??.)(?:'.implode('|',$options['allowedExternalImageExts']).'))(\\?[^ \n]+|)/i';
			$extImgOptionPattern = '/[&?]((width|height|align)=(left|center|right|[0-9]+(?:%|px|)))/';
			if(str_starts_with(substr($line, $j),'http') && preg_match($extImgPattern, $line, $matches) && strpos($line,$matches[0]) === 0){
				$imgUrl = $matches[1];
				$optionsString = $matches[2];
				$optionMatches = preg_match_all($extImgOptionPattern, $optionsString);
				if (!is_array($optionMatches[1])) $optionMatches = array(null, array());
				$styleOptions = array();
				for($k=1;$k<count($optionMatches[1]);$k++){
					$styleOptions[$optionMatches[1][$k]] = $optionMatches[2][$k];
				}
				if(strlen($plainTemp) !== 0){
					array_push($result, array('name' => 'plain', 'text' => $plainTemp));
					$plainTemp = '';
				}
				array_push($result, array('name' => 'external-image', 'style'=> $styleOptions, 'target' => $imgUrl));
				$j += mb_strlen($matches[0]) -1;
				continue;
			}else{
				$nj = $j;
				$matched = false;
				for($k=0;$k<count($singleBrackets);$k++){
					$bracket = $singleBrackets[$k];
					$temp = null;
					$innerStrLen = null;
					if(str_starts_with(substr($line,$j),$bracket['open']) && $br_i = bracketParser($line, $nj, $bracket) && $temp = $br_i[0] && $nj = $br_i[1] && $innerStrLen = $br_i[2]){
						if(strlen($plainTemp) !== 0){
							array_push($result, array('name' => 'plain', 'text' => $plainTemp));
							$plainTemp = '';
						}
						$result = array_push($result, $temp);
						$j += $innerStrLen - 1;
						$matched = true;
						break;
					}
				}
				if(!$matched){
					if($line[$j] == '\n'){
						array_push($result, array('name' => 'plain', 'text' => $plainTemp));
						$plainTemp = '';
					}else{
						$plainTemp .= $line[j];
					}
				}
			}
		}
		if(strlen($plainTemp) != 0) {
			array_push($result, array('name' => 'plain', 'text' => $plainTemp));
			$plainTemp = '';
		}
		return $result;
	}

	function lineParser($line){
		$result = array();
		$headings = array(
			'/^= (.+) =$/' => 1,
			'/^== (.+) ==$/' => 2,
			'/^=== (.+) ===$/' => 3,
			'/^==== (.+) ====$/' => 4,
			'/^===== (.+) =====$/' => 5,
			'/^====== (.+) ======$/' => 6
		);

		if(str_starts_with($line, '##'))
		    return array(array('name' => 'comment', 'text' => substr($line,2)));

		if(str_starts_with($line, '=')){
			foreach($headings as $patternString){
				if(preg_match($patternString, $line, $hd_m)){
					$level = $headings[$patternString];
					return array(array('name' => 'heading-start', 'level' => $level), array('name' => 'wikitext', 'treatAsBlock' => true, 'text' => $hd_m[1]), array('name' => 'heading-end'));
				}
			}
		}

		if(!preg_match('/[^-]/', $line) && strlen($line) >= 4 && strlen($line) <= 10){
			return array(array('name' => 'horizontal-line'));
		}

		if(strlen($line) !=0)
		    return array(array('name' => 'paragraph-start'), blockParser($line), array('name' => 'paragraph-end'));
		else
		    return array();
	}
	
	function bracketParser($wikitext, $pos, $bracket, $cb){
		function callProc($proc, $arg1=null, $arg2=null, $arg3=null, $arg4=null){
		switch($proc){
			case 'render':
				return renderProcessor($arg1, $arg2);
				break;
			case '':
			default:
				break;
			}
		}
		$cnt = 0;
		$done = false;
		for($i=$pos;$i<mb_strlen($wikitext);$i++){
			if(str_starts_with(substr($wikitext, $i), $bracket['open']) && !($bracket['open'] == $bracket['close'] && $cnt > 0)){
				$cnt++;
				$done = true;
				$i += strlen($bracket['open']) - 1;
			} elseif(str_starts_with(substr($wikitext, $i), $bracket['close'])){
				$cnt--;
				$i += strlen($bracket['close']) - 1;
			} elseif(!$bracket['multiline'] && strpos($wikitext,$i) === '\n')
				return null;
			
			if($cnt == 0 && $done){
				($cb)?$re = mb_strlen($innerString) + strlen($bracket['open'] + strlen($bracket['close']):$re = null;
				$innerString = substr($wikitext, $pos + strlen($bracket['open']), $i - strlen($bracket['close']) + 1);
				return array(callProc('render', $innerString, $bracket['open']), $i, $re);
			}
		}
		return null;
	}
	function blockquoteParser($wikitext, $pos){
		$temp = array();
		$result = array();
		for($i=$pos;$i<mb_strlen($wikitext);$i=seekEOL($wikitext, $i)+1){
			$eol = seekEOL($wikitext, $i);
			if(!str_starts_with(substr($wikitext, $i), '>'))
				break;
			preg_match('/^>+/', substr($wikitext,$i), $bq_match);
			$level = mb_strlen($bq_match[0]);
			$line = substr($wikitext, $i+$level, $eol);
			array_push($temp, array('level' => $level, 'line' => $line));
		}
		if(count($temp) == 0)
			return null;
		$curLevel = 1;
		array_push($result, array('name' => 'blockquote-start'));
		for($i=0; $i<count($temp); $i++){
			$curTemp = $temp[$i];
			if($curTemp['level'] > $curLevel){
				for($i=0; $i<$curTemp['level'] - $curLevel; $i++)
					array_push($result, array('name' => 'blockquote-start'));
			} elseif($curTemp['level'] < $curLevel){
				for($i=0; $i<$curLevel - $curTemp['level']; $i++)
					array_push($result, array('name' => 'blockquote-end'));
			} else{
				array_push($result, array('name' => 'new-line'));
			}
			array_push($result, array('name' => 'wikitext', 'parseFormat' => true, 'text' => $curTemp['line']));
		}
		array_push($result, array('name' => 'blockquote-end'));
		return array($result, $i - 1);
	}
	function finishTokens($tokens){
		$result = array();
		$prevListLevel = 0;
		$prevIndentLevel = 0;
		$prevWasList = false;
		for($i=0; $i<count($tokens); $i++){
			$curToken = $tokens[$i];
			$curWasList = $curToken['name'] === 'list-item-temp';
			if($curWasList != $prevWasList) {
				for($j=0; $j< ($prevWasList)?$prevListLevel:$prevIndentLevel; $j++)
					array_push($result, array('name' => ($prevWasList)?'list-end':'indent-end';));
				if($prevWasList) $prevListLevel = 0;
				else $prevIndentLevel = 0;
			}
			switch($curToken['name']){
				case 'list-item-temp':
					if($prevListLevel < $curToken['level']) {
						for($j=0; $j < $curToken['level'] - $prevListLevel; $j++)
							array_push($result, array('name' => 'list-start', 'listType' => $curToken['listType']));
					} elseif ($prevListLevel > $curToken['level']){
						for($j=0; $j < $prevListLevel - $curToken['level']; $j++)
							array_push($result, array('name' => 'list-end'));
					} elseif($prevListType['ordered'] !== $curToken['listType']['ordered'] || $prevListType['type'] !== $curToken['listType']['type']){
						array_push($result, array('name' => 'list-end'));
						array_push($result, array('name' => 'list-start', 'listType' => $curToken['listType']));
					}
					$prevListLevel = $curToken['level'];
					$prevListType = $curToken['listtype'];
					array_push($result, array('name' => 'list-item-start', 'startNo' => ($curToken['startNo'])?$curToken['startNo']:null;));
					### listParser.js 50행
			}
		}
	}
	function listParser($wikitext, $pos){
		$listTags = array(
			'*' => array('ordered' => false),
			'1.' => array('ordered' => true, 'type' => 'decimal'),
			'A.' => array('ordered' => true, 'type' => 'upper-alpha'),
			'a.' => array('ordered' => true, 'type' => 'lower-alpha'),
			'I.' => array('ordered' => true, 'type' => 'upper-roman'),
			'i.' => array('ordered' => true, 'type' => 'lower-roman')
		);
	}

    	function parse(){return $this->doParse();}
	function setIncluded(){$options['included'] = true;}
	function setIncludeParameters($paramsObj){$options['includeParameters'] = $paramsObj;}
	function setRenderer($r = null, $o = null){
		if($r !==null) $rendererClass = $r;
		if($o !==null) $rendererOptions = $o;
		return;
	}
}
