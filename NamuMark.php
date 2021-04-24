<?php
require_once 'HTMLRenderer.php';
require_once 'PressDoLib.php';
class NamuMark{
	public $redirectPattern = '/^#(?:redirect|넘겨주기) (.+)$/im';
	public function __construct($content, $_to_content, $_options=array()){
		if($_to_content == 1){
			$content = Data::LoadLatestDocument($content);
		}
		$this->defaultOptions = array(
			'wiki' => $content,
			'allowedExternalImageExts' => $_options,
			'included' => false,
			'includeParameters' => array(),
			'macroNames' => array('br', 'date', '목차', 'tableofcontents', '각주', 'footnote', 'toc', 'youtube', 'nicovideo', 'kakaotv', 'include', 'age', 'dday')
		);
		$options = $this->defaultOptions;
		$this->wikitext = $options['wiki']['read'];
		$this->rendererOptions = null;
		$this->renderer = null;
	}
	private function seekEOL($text, $offset = 0){
		return (strpos($text, '\n', $offset) == -1 )? mb_strlen($text) : strpos($text, '\n', $offset);
	}		
	private function doParse($callback){
		$multiBrackets = array(
			'open' => '{{{',
			'close' => '}}}',
			'multiline' => true,
			'processor' => 'renderProcessor'
		);
		$this->renderer = ($this->rendererOptions)? new HTMLRenderer($this->rendererOptions):new HTMLRenderer();
		$line = '';
		$now = '';
		$tokens = array();
		if($this->wikitext === null)
		    return array(['name' => 'error', 'type' => 'notfound']);
		if(str_starts_with($this->wikitext, '#') && preg_match($this->redirectPattern, $this->wikitext, $r_match) && strpos($this->wikitext, $r_match[0]) === 0)
	        return array(['name' => 'redirect', 'target' => $r_match[1]]);
		for($i=0;$i < mb_strlen($this->wikitext); $i++){
			$temp = array('pos' => $i);
			$now = substr($this->wikitext,$i,1);

			// func[0] = Gijon gap, func[1]: v => i = v
			if($line == '' && $now == ' ' && $temp = listParser($this->wikitext, $i, fn($v) => $i = $v)){
				$tokens = array_push($tokens, $temp);
				$line = '';
				$now = '';
				continue;
			}
			if($line == '' && str_starts_with(substr($this->wikitext, $i), '|') && $temp = tableParser($this->wikitext, $i, fn($v)=>$i = $v)){
				$tokens = array_push($tokens, $temp);
				$line = '';
				$now = '';
				continue;
			}
			if($line == '' && str_starts_with(substr($this->wikitext, $i), '>') && $temp = blockquoteParser($this->wikitext, $i, fn($v) => $i = $v, $callProcessor)){
				$tokens = array_push($tokens, $temp);
				$line = '';
				$now = '';
				continue;
			}
			foreach($multiBrackets as $bracket){
				// Callproc moved into func
				if(str_starts_with(substr($this->wikitext, $i), $bracket['open']) && $temp = bracketParser($this->wikitext, $i, fn($v) => $i = $v)){
					$tokens = array_push($tokens, array(['name' => 'wikitext', 'treatAsLine' => true, 'text' => $line]),$temp);
					$line = '';
					$now = '';
					break;
				}
			}
			if($now === '\n'){
				$tokens = array_push($tokens, array(['name' => 'wikitext', 'treatAsLine' => true, 'text' => $line]));
			    $line = '';
			}
			else
			    $line .= $now;
		}
		if(mb_strlen($line) != 0)
		    $tokens = array_push($tokens,array(['name' => 'wikitext', 'treatAsLine' => true, 'text' => $line]));
		function processTokens($newarr){
			if(!is_array($newarr)) $newarr = [];
			for($i=0;$i < count($newarr); $i++){
				$v = $newarr[$i];
				if(is_array($v))
				    processTokens($v);
				elseif($v['name'] !== 'wikitext')
				    $this->renderer->processToken($v);
				elseif($v['parseFormat'] || $v['treatAsBlock'])
				    processTokens(blockParser($v['text']));
				elseif($v['treatAsLine'])
				    processTokens(lineParser($v['text']));
			}
		}
		processTokens($tokens);
		$this->renderer->getResult(function($err, $result) {if($err) $callback($err); else $callback(null, $result);});
	}

	function blockParser($line){
		$result = array();
		$s_formats = ["'''", "''", '~~', '--', '__', '^^',',,'];
		$s_result = [];
		for($i=0; $i < count($s_formats); $i++) {
			array_push($s_result, array(
				'open' => $s_formats[$i],
				'close' => $s_formats[$i],
				'multiline' => false,
				'processor' => 'textProcessor'
			));
		}
		$singleBrackets = array_merge(array(
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
		), $s_result);
		$plainTemp = '';
		for($j=0;$j<mb_strlen($line);$j++){
			$extImgPattern = '/(https?:\\/\\/[^ \\n]+(?:\\??.)(?:'.implode('|',$options['allowedExternalImageExts']).'))(\\?[^ \n]+|)/i';
			$extImgOptionPattern = '/[&?]((width|height|align)=(left|center|right|[0-9]+(?:%|px|)))/';
			if(str_starts_with(substr($line, $j),'http') && preg_match($extImgPattern, $line, $matches) && strpos($line,$matches[0]) === 0){
				$imgUrl = $matches[1];
				$optionsString = $matches[2];
				$optionMatches = preg_match_all($extImgOptionPattern, $optionsString);
				if (!is_array($optionMatches[1])) $optionMatches = array(null, []);
				$styleOptions = [];
				for($k=1;$k<count($optionMatches[1]);$k++){
					$styleOptions[$optionMatches[1][$k]] = $optionMatches[2][$k];
				}
				if(strlen($plainTemp) !== 0){
					array_push($result, array('name' => 'plain', 'text' => $plainTemp));
					$plainTemp = '';
				}
				array_push($result, ['name' => 'external-image', 'style'=> $styleOptions, 'target' => $imgUrl]);
				$j += mb_strlen($matches[0]) -1;
				continue;
			}else{
				$nj = $j;
				$matched = false;
				for($k=0;$k<count($singleBrackets);$k++){
					$bracket = $singleBrackets[$k];
					$temp = null;
					$innerStrLen = null;
					if(str_starts_with(substr($line,$j),$bracket['open']) && ($temp = bracketParser($line, $nj, $bracket, fn($v) => $nj = $v, $callProcessor, fn($v) => $innerStrLen = $v))){
						if(strlen($plainTemp) !== 0){
							array_push($result, ['name' => 'plain', 'text' => $plainTemp]);
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

        $callProcessor = function($proc, $args){
		return $this->$proc($args[1], $args[2]);
	}
	
	function bracketParser($wikitext, $pos, $bracket, $setpos, $callProc, $matchLenCallback=null){
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
				$innerString = substr($wikitext, $pos + strlen($bracket['open']), $i - strlen($bracket['close']) + 1);
				if($matchLenCallback) $matchLenCallback(mb_strlen($innerString) + strlen($bracket['open'] + strlen($bracket['close']));
                                $setpos($i);
                                return $callProc($bracket['processor'], array($innerString, $bracket['open']));
			}
		}
		return null;
	}
	function blockquoteParser($wikitext, $pos, $setpos){
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
		$setpos($i-1);
                return $result;
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
					array_push($result, array('name' => 'wikitext', 'treatAsBlock' => true, 'text' => $curToken['wikitext']));
					array_push($result, array('name' => 'list-item-end'));
					break;
				case 'indent-temp':
					if($prevIndentLevel < $curToken['level']) {
						for($j=0; $j<$curToken['level'] - $prevIndentLevel; $j++)
							array_push($result, array('name' => 'indent-start'));
					} elseif($prevIndentLevel > $curToken['level']) {
						for($j=0; $j<$prevIndentLevel - $curToken['level']; $j++)
							array_push($result, array('name' => 'indent-end'));
					}
					$prevIndentLevel = $curToken['level'];
					array_push($result, array('name' => 'wikitext', 'treatAsBlock' => true, 'text' => $curToken['wikitext']));
					### listParser.js 50행
			}
			if($i === count($tokens) - 1){
				if($curWasList) {
					for($j=0; $j < $prevListLevel; $j++)
						array_push($result, array('name' => 'list-end'));
				} else{
					for($j=0; $j < $prevIndentLevel; $j++)
						array_push($result, array('name' => 'indent-end'));
				}
			}
			$prevWasList = $curWasList;
		}
		return $result;
	}
	function listParser($wikitext, $pos, $setpos){
		$listTags = array(
			'*' => array('ordered' => false),
			'1.' => array('ordered' => true, 'type' => 'decimal'),
			'A.' => array('ordered' => true, 'type' => 'upper-alpha'),
			'a.' => array('ordered' => true, 'type' => 'lower-alpha'),
			'I.' => array('ordered' => true, 'type' => 'upper-roman'),
			'i.' => array('ordered' => true, 'type' => 'lower-roman')
		);
		$lineStart = $pos;
		$result = array();
		$isList = null;
		for($i=$pos; $i<mb_strlen($wikitext); $i++){
			$char = substr($wikitext, $i, 1);
			if($char != ' ') {
				if($lineStart === $i)
					break;
				$level = $i - $lineStart;
				$matched = false;
				$quit = false;
				$eol = seekEOL($wikitext, $i);
				$innerString = substr($wikitext, $i, $eol);
				for($listTags as $j){
					$listTagInfo = $listTags[$j];
					$innerString = substr($wikitext, $i + strlen($j), $eol);
					preg_match('/'.preg_replace('/\*/g', '\\*', preg_replace('/\./g', '\\.', $j)).'#([0-9]+)/', substr($wikitext, $i), $startNoSpecifiedPattern);
					if(str_starts_with(substr($wikitext, $i), $j)){
						if($isList === null)
							$isList = true;
						elseif(!$isList) {
							quit = true;
							break;
						}
						$matched = true;
						if($startNoSpecifiedPattern){
							$startNo = intval($startNoSpecifiedPattern[1]);
							$innerString = preg_replace('/^#[0-9]+/', '', $innerString);
							array_push($result, array('name' => 'list-item-temp', 'listType' => $listTagInfo, 'level' => $level, 'startNo' => $startNo, 'wikitext' => $innerString));
						} else {
							array_push($result, array('name' => 'list-item-temp', 'listType' => $listTagInfo, 'level' => $level, 'wikitext' => $innerString));
						}
						$i = $eol;
						$lineStart = $eol + 1;
						break;
					}
				}
				if($quit){
					$i = $lineStart;
					break;
				}
				if(!$matched){
					if($isList === null){
						$isList = false;
					} elseif($isList) {
						$i = $lineStart;
						break;
					}
					array_push($result, array('name' => 'indent-temp', 'level' => $level, 'wikitext' => $innerString));
					$i = $eol;
					$char = "\n";
				}
			}
			if($char == '\n')
				$lineStart = $i + 1;
		}
		if(count($result) === 0)
			$result = null;
			$setpos = null;
		else{
			$result = finishTokens($result);
			$setpos($i - 1);
		}
		return $result;
	}
	function parseOptionBracket($optionContent){
		$colspan = 0;
		$rowspan = 0;
		$colOption = array();
		$tableOptions = array();
		$rowOptions = array();
		$matched = false;
		if(preg_match('/^-[0-9]+$/', $optionContent, $colspan_str)) {
			$colspan += intval($colspan_str);
			$matched = true;
		} elseif(preg_match('/^\|([0-9]+)$/', $optionContent, $rowspan_mid) || preg_match('/^\^\|([0-9]+)$/', $optionContent, $rowspan_top) || preg_match('/^v\|([0-9]+)$/', $optionContent, $rowspan_bot)){
			$matched = true;
			if($rowspan_mid){
				$rowspan += intval($rowspan_mid[1]);
				$colOptions['vertical-align'] = 'middle';
			} elseif($rowspan_top){
				$rowspan += intval($rowspan_top[1]);
				$colOptions['vertical-align'] = 'top';
			} elseif($rowspan_bot){
				$rowspan += intval($rowspan_top[1]);
				$colOptions['vertical-align'] = 'top';
			}
		} elseif(str_starts_with($optionContent, 'table ')) {
			$tableOptionContent = substr($optionContent, 6);
			$tableOptionPatterns = array(
				'align' => '/^align=(left|center|right)$/',
				'background-color' => '/^bgcolor=(#[a-zA-Z0-9]{3,6}|[a-zA-Z]+)$/',
				'border-color' => '/^bordercolor=(#[a-zA-Z0-9]{3,6}|[a-zA-Z]+)$/',
				'width' => '/^width=([0-9]+(?:in|pt|pc|mm|cm|px))$/'
			);
			foreach($tableOptionPatterns as $optionName){
				if(preg_match($tableOptionPatterns[$optionName], $tableOptionContent, $t_top)){
					$tableOptions[$optionName] = $t_top[1];
					$matched = true;
				}
			}
		} else {
			$textAlignCellOptions = array(
				'left' => '/^\($/',
				'middle' => '/^:$/',
				'right' => '/^\)$/'
			);
			$paramlessCellOptions = array(
				'background-color' => '/^bgcolor=(#[0-9a-zA-Z]{3,6}|[a-zA-Z0-9]+)$/',
				'row-background-color' => '/^rowbgcolor=(#[0-9a-zA-Z]{3,6}|[a-zA-Z0-9]+)$/',
				'width' => '/^width=([0-9]+(?:in|pt|pc|mm|cm|px|%))$/',
				'height' => '/^height=([0-9]+(?:in|pt|pc|mm|cm|px|%))$/'
			);
			foreach ($textAlignCellOptions as $i) {
				if(preg_match($textAlignCellOptions[$i], $optionContent)){
					$colOptions['text-align'] = $optionContent;
					$matched = true;
				}
				else
					foreach($paramlessCellOptions as $optionName){
						if(!preg_match($paramlessCellOptions[$optionName], $optionContent, $_prg_rop))
							continue;
						if(str_starts_with($optionName, 'row-'))
							$rowOptions[substr($optionName, 4)] = $_prg_rop[1];
						else
							$colOptions[$optionName] = $_prg_rop[1];
						$matched = true;
					}
			}
		}
		return array('colspan_add' => $colspan, 'rowspan_add' => $rowspan, 'colOptions_set' => $colOptions, 'rowOptions_set' => $rowOptions, 'tableOptions_set' => $tableOptions, 'matched' => $matched);
	}
	function tableParser($wikitext, $pos, $setpos) {
		$caption = null;
		if(!str_starts_with(substr($wikitext, $pos), '||')){
			$caption = substr($wikitext, $pos + 1, strpos($wikitext, '|', $pos + 2));
			$pos = strpos($wikitext, '|', $pos + 1) + 1;
			// echo $caption;
		} else {
			$pos += 2;
		}
		$cols = explode('||', $substr($wikitext, $pos));
		$rowno = 0;
		$hasTableContent = false;
		$colspan = 0;
		$rowspan = 0;
		$optionPattern = '/<(.+?)>/';
		// echo $cols;
		$table = array(array());
		$tableOptions = array();
		if(count($cols) < 2)
			return null;
		for ($i=0; $i<count($cols); $i++) {
			$col = $cols[$i];
			$curColOptions = array();
			$rowOption = array();
			if(str_starts_with($col, '\n') && strlen($col) > 1){
				break;
			}
			if($col == '\n'){
				$table[++$rowno] = array();
				continue;
			}
			if(strlen($col) == 0){
				$colspan++;
				continue;
			}
			
			if(str_starts_with($col, ' ') && !str_ends_with($col, ' '))
				$curColOptions['text-align'] = 'left';
			elseif(!str_starts_with($col, ' ') && str_ends_with($col, ' '))
				$curColOptions['text-align'] = 'right';
			elseif(str_starts_with($col, ' ') && str_ends_with($col, ' '))
				$curColOptions['text-align'] = 'middle';
			
			while (preg_match($optionPattern, $col, $match){
				if($strpos($match) != 0)
					break;
				$optionContent = $match[1];
				$pO = parseOptionBracket($optionContent);
				$colOptions_set = $pO['colOptions_set'];
				$tableOptions_set = $pO['tableOptions_set'];
				$colspan_add = $pO['colspan_add'];
				$rowspan_add = $pO['rowspan_add'];
				$rowOptions_set = $pO['rowOptions_set'];
				$matched = $pO['matched'];
				$curColOptions = array_merge($curColOptions, $colOptions_set);
				$tableOptions = array_merge($tableOptions, $colOptions_set);
				$rowOptions_set = array_merge($rowOption, $rowOptions_set);
				
				$colspan += $colspan_add;
				$rowspan += $rowspan_add;
				
				if($tableOptions['border-color']){
					$tableOptions['border'] = '2px solid '.$tableOptions['border-color'];
					unset($tableOptions['border-color']);
				}
				
				$col = substr($col, mb_strlen($match[0]));
			}
			$colObj = array(
				'options' => $curColOptions,
				'colspan' => $colspan,
				'rowspan' => $rowspan,
				'rowOption' => $rowOption,
				'wikitext'=> $col
			);
			$colspan = 0;
			$rowspan = 0;
			array_push($table[$rowno], $colObj);
			$hasTableContent = true;
		}
		$rowOptions = array();
		for($j=0; $j<count($table); $j++){
			$rowOption = array();
			for($k=0; $k<count($table[$j]); $k++){
				$rowOption = array_merge($rowOption, $table[$j]['rowOption']);
			}
			array_push($rowOption, $rowOption);
		}
			       
		$result = array(array('name' => 'table-start', 'options' => $tableOptions));
		$rowCount = count($table);
		for($j=0; $j<$rowCount; $j++){
			array_push($result, array('name' => 'table-row-start', 'options' => $rowOptions[$j]));
			for($k=0; $k<count($table[$j]); $k++){
				array_push($result, array('name' => 'table-col-start', 'options' => $table[$j][$k]['colspan'], 'rowspan' =>  $table[$j][$k]['rowspan']));
				array_push($result, array('name' => 'wikitext', 'text' => $table[$j][$k]['wikitext'], 'treatAsLine' => true));
				array_push($result, array('name' => 'table-col-end'));
			}
			array_push($result, array('name' => 'table-row-end'));
		}
		array_push($result, array('name' => 'table-end'));
		if($hasTableContent){
			$setpos(mb_strlen($pos + implode('||', array_slice($cols, 0, $i))) + 1);
			return $result;
		}else{
			return null;
		}
	}
	function closureProcessor($text, $type){
		return array(array('name' => 'closure-start'), array('name' => 'wikitext', 'parseFormat' => true, 'text' => $text), array('name' => 'closure-end'));
	}
	function linkProcessor($text, $type){
		$href = explode('|', $text);
		if(preg_match('/^https?:\/\//', $text)){
			if(count($href) > 1){
				return array(array(
					'name' => 'link-start',
					'external' => true,
					'target' => $href[0]
				), array(
					'name' => 'wikitext',
					'parseFormat' => true,
					'text' => $href[1]
				));
			}else{
				return array(array(
					'name' => 'link-start',
					'external' => true,
					'target' => $href[0]
				), array(
					'name' => 'plain',
					'parseFormat' => true,
					'text' => $href[0]
				));
			}
		} elseif(preg_match('/^분류:(.+)$/', $href[0], $category)){
			if(!$this->defaultOptions['included'])
				return array(array(
					'name' => 'add-category',
					'blur' => str_ends_with('#blur'),
					'categoryName' => $category
				));
		} elseif (preg_match('/^파일:(.+)$/', $href[0], $h_file)){
			$fileOpts = array();
			$haveOpts = false;
			if(count($href) > 1){
				$pattern = '/[&?]?(^[=]+)=([^\&]+)/g';
				$match = null;
				while(preg_match($pattern, $href[1], $match)){
					if(($match[1] === 'width' || $match[1] === 'height') && preg_match('/^[0-9]$/', $match[2])) {
						$match[2] = $match[2].'px';
					}
					$fileOpts[$match[1]] = $match[2];
					$haveOpts = true;
				}
			}
			if($haveOpts) {
				return array(array(
					'name' => 'image',
					'taget' => $h_file[1]
				));
			} else {
				return array(array(
					'name' => 'image',
					'taget' => $h_file[1],
					'options' => $fileOpts
				));
			}
		} else {
			if(str_starts_with($href[0], ' ') || str_starts_with($href[0], ':')){
				$href[0] = substr($href[0], 1);
			}
			if(count($href) > 1){
				return array(array(
					'name' => 'link-start',
					'internal' => true,
					'target' => $href[0]
				), array(
					'name' => 'wikitext',
					'parseFormat' => true,
					'text' => $href[1]
				), array(
					'name' => 'link-end'
				));
			} else {
				return array(array(
					'name' => 'link-start',
					'internal' => true,
					'target' => $href[0]
				), array(
					'name' => 'plain',
					'text' => $href[0]
				), array(
					'name' => 'link-end'
				));
			}
		}
	}
	function macroProcessor($text, $type) {
		$defaultResult = array(array('name' => 'plain', 'text' => '['.$text.']'));
		if(str_starts_with($text, '*') && preg_match('/^\*([^ ]*) (.+)$/', $text, $matches)) {
			(strlen($matches[1]) === 0)? $supText = null: $supText = $matches[1];
			return array(array(
				'name' => 'footnote-start',
				'supText' => $supText
			), array(
				'name' => 'wikitext',
				'treatAsBlock' => true,
				'text' => $matches[2]
			), array(
				'name' => 'footnote-end'
			));
		} else {
			if(preg_match('/^[^\(]+$/', $text)){
				if(!array_search($text, $this->defaultOptions['macroNames']))
					return $defaultResult;
				else
					return array(array('name' => 'macro', 'macroName' => $text));
			} elseif(preg_match('/^([^\(]+)\((.*)\)/', $text, $matches)){
				if(!array_search($matches[1], $this->defaultOptions['macroNames']))
					return $defaultResult;
				$macroName = $matches[1];
				$optionSplitted = explode(',', $matches[2]);
				$options = array();
				if(strlen($matches[2]) != 0) {
					foreach($optionSplitted as $i){
						if(!array_search('=', $i)){
							array_push($options, $i);
						} else {
							$_m_i_v = explode('=', $i);
							array_push($options, array('name' => $_m_i_v[0], 'value' => $_m_i_v[1]));
						}
					}
				}
				return array(array('name' => 'macro', 'macroName' => $macroName, 'options', => $options));
			}
			return $defaultResult;
		}
	}
	function renderProcessor($text, $type){
		if(preg_match('/^#!html/i', $text)){
			return array(array(
				'name' => 'unsafe-plain',
				'text' => substr($text, 6)
			));
		} elseif (preg_match('/^#!folding/i', $text) && strpos($text, '\n') >= 10) {
			return array(array(
				'name' => 'folding-start',
				'summary' => substr($text, 10, strpos($text, '\n'))
			), array(
				'name' => 'wikitext',
				'treatAsBlock' => true,
				'text' => substr($text, strpos($text, '\n') + 1)
			), array(
				'name' => 'folding-end'
			));
		} elseif (preg_match('/^#!syntax/i', $text) && strpos($text, '\n') >= 9) {
			return array(array(
				'name' => 'syntax-highlighting',
				'header' => substr($text, 9, strpos($text, '\n')),
				'body' => substr($text, strpos($text, '\n') + 1)
			));
		} elseif (preg_match('/^#!wiki/i', $text)) {
			if(strpos($text, '\n') >= 7) {
				$params = substr($text, 7, strpos($text, '\n'));
				if(str_starts_with($params, "style=\"") && preg_match('/" +$/', $params, $match)){
					return array(array(
						'name' => 'wiki-box-start',
						'style' => substr($params, 7, mb_strlen($params) - mb_strlen($match[0]))
					), array(
						'name' => 'wikitext',
						'treatAsBlock' => true,
						'text' => substr($text, strpos($text, '\n') + 1)
					), array(
						'name' => 'wiki-box-end'
					));
				} else {
					return array(array(
						'name' => 'wiki-box-start',
					), array(
						'name' => 'wikitext',
						'treatAsBlock' => true,
						'text' => substr($text, strpos($text, '\n') + 1)
					), array(
						'name' => 'wiki-box-end'
					));
				}
			}
		} elseif (preg_match('/^#([A-Fa-f0-9]{3,6}) (.*)$/', $text, $matches)){
			if(strlen($matches[1]) === 0 && strlen($matches[2]) === 0)
				return array(array(
					'name' => 'plain',
					'text' => $text
				));
			return array(array(
				'name' => 'font-color-start',
				'color' => $matches[1]
			), array(
				'name' => 'wikitext',
				'parseFormat' => true,
				'text' => $matches[2]
			), array(
				'name' => 'font-color-end'
			));
		} elseif (preg_match('/^\+([1-5]) (.*)$/', $text, $matches)){
			return array(array(
				'name' => 'font-size-start',
				'color' => $matches[1]
			), array(
				'name' => 'wikitext',
				'parseFormat' => true,
				'text' => $matches[2]
			), array(
				'name' => 'font-size-end'
			));
		}
		return array(array(
			'name' => 'monoscape-font-start',
			'pre' => true
		), array(
			'name' => 'plain',
			'text' => substr($text, 1)
		), array(
			'name' => 'monoscape-font-end'
		));
	}
	function textProcessor($text, $type){
		$styles = array(
			"'''" => 'strong',
			"''" => 'italic',
			'--' => 'strike',
			'~~' => 'strike',
			'__' => 'underline',
			'^^' => 'superscript',
			',,' => 'subscript'
		);
		switch($type){
			case "'''":
			case "''":
			case '--':
			case '~~':
			case '__':
			case '^^':
			case ',,':
				return array(array('name' => $styles[$type].'-start'), array('name' => 'wikitext', 'parseFormat' => true, 'text' => $text), array('name' => $styles[$type].'-end'));
			case '{{{':
				if(str_starts_with($text, '#!html')){
					return array(array('name' => 'unsafe-plain', 'text' => substr($text, 6)));
				} elseif (preg_match('/^#([A-Fa-f0-9]{3,6}) (.*)$/', $text, $matches)){
					if(strlen($matches[1]) === 0 && strlen($matches[2]) === 0)
						return array(array('name' => 'plain','text' => $text));
					return array(array(
						'name' => 'font-color-start',
						'color' => $matches[1]
					), array(
						'name' => 'wikitext',
						'parseFormat' => true,
						'text' => $matches[2]
					), array(
						'name' => 'font-color-end'
					));
				} elseif (preg_match('/^\+([1-5]) (.*)$/', $text, $matches)) {
					return array(array(
						'name' => 'font-size-start',
						'color' => $matches[1]
					), array(
						'name' => 'wikitext',
						'parseFormat' => true,
						'text' => $matches[2]
					), array(
						'name' => 'font-size-end'
					)); 
				}
				return array(array('name' => 'monoscape-font-start','pre' => true), array('name' => 'plain','text' => substr($text, 1)), array('name' => 'monoscape-font-end'));
			case '@':
				if(!$this->defaultOptions['included'])
					break;
				if(array_search($text, array_keys($this->defaultOptions['includeParameters'])))
					return array(array('name' => 'wikitext', 'parseFormat' => true, 'text' => $this->defaultOptions['includeParameters'][$text]));
				else
					return null;
		}
		return array(array('name' => 'plain', 'text' => $type.$text.$type));
	}

    	$this->parse = fn($c) => {return $this->doParse($c);}
        $this->setIncluded = fn() =>{$this->defaultOptions['included'] = true;}
	$this->setIncludeParameters = fn($paramsObj) => {$this->defaultOptions['includeParameters'] = $paramsObj;}
	$this->setRenderer = fn($r = null, $o = null) => {
		if($r !==null) $this->rendererClass = $r;
		if($o !==null) $this->rendererOptions = $o;
		return;
	}
}
