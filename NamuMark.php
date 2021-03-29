< ?php
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

    function parse(){return $this->doParse();}
	function setIncluded(){$options['included'] = true;}
	function setIncludeParameters($paramsObj){$options['includeParameters'] = $paramsObj;}
	function setRenderer($r = null, $o = null){
		if($r !==null) $rendererClass = $r;
		if($o !==null) $rendererOptions = $o;
		return;
	}
}
