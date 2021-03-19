<?php
// 745행 인근 https://attachment.namu.wiki/ 수정필요
/**
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
 * TheWiki 코드 일부 사용됨
 * 코드 설명 주석 추가: PRASEOD-
 * 설명 주석이 +로 시작하는 경우 PRASEOD-의 2차 수정 과정에서 추가된 코드입니다.
 * 
 * ::::::::: 변경 사항 ::::::::::
 * 카카오TV 영상 문법 추가 [나무위키]
 * 커스텀 영상 문법 추가 [video(url)]
 * 문단 문법 미작동 문제 수정
 * 일부 태그 속성 수정
 * 글씨크기 관련 문법 수정
 * {{{#!wiki }}} 문법 오류 수정
 * anchor 문법 추가
 * 테이블 글씨색 속성 추가
 * 수평선 문법 미작동 오류 수정
 * <nowiki>, <pre> 태그 <code>로 대체
 * 취소선 태그 <s>에서 <del>로 변경
 * 본문 영역 문단별 <div> 적용
 * 접힌목차 기능 추가
 * 볼드체 문법 추가(**)
 * 
 * :: Bugs ::
 * 맨 처음 여는 {{{#!wiki }}} 괄호에서 이중 따옴표로 인해 스타일이 적용되지 않음
 * 텍스트 + 표를 하나의 {{{#!wiki }}}로 감쌀 경우 표 문법이 적용되지 않음
 * 표 td에 적용된 {{{#!wiki }}} 내부에서 개행 시 표 문법에 영향을 줌
 */

class PlainWikiPage {
	// 평문 데이터 호출
	public $title, $text, $lastchanged;
	function __construct($text) {
		$this->title = '(inline wikitext)';
		$this->text = $text;
		$this->lastchanged = time();
	}

	function getPage($name) {
		return new PlainWikiPage('');
	}
}

class MySQLWikiPage {
	// DB에서 평문 데이터 호출
	public $title, $text, $lastchanged;
	private $sql;
	function __construct($name, $_mysql) {
		if(!($result = $_mysql->query('SELECT `text`, `lastchanged` FROM `documents` WHERE `document` = "'.$_mysql->real_escape_string($name).'"'))) {
			return false;
		}

		if(!($row = $result->fetch_array(MYSQLI_NUM))) {
			return false;
		}
		$this->title = $name;
		$this->text = $row[0];
		$this->lastchanged = $row[1]?strtotime($row[1]):false;
		$this->sql = $_mysql;
	}

	function getPage($name) {
		return new MySQLWikiPage($name, $this->sql);
	}
}

class NamuMark {
	public $prefix, $lastchange;

	function __construct($wtext) {
		// 문법 데이터 생성
		$this->list_tag = array(
			array('*', 'ul pressdo-ul'),
			array('1.', 'ol pressdo-ol pressdo-ol-numeric'),
			array('A.', 'ol pressdo-ol pressdo-ol-capitalised'),
			array('a.', 'ol pressdo-ol pressdo-ol-alphabetical'),
			array('I.', 'ol pressdo-ol pressdo-ol-caproman'),
			array('i.', 'ol pressdo-ol pressdo-ol-lowroman')
			);

		$this->h_tag = array(
			array('/^======(.*)======/', 6),
			array('/^=====(.*)=====/', 5),
			array('/^====(.*)====/', 4),
			array('/^===(.*)===/', 3),
			array('/^==(.*)==/', 2),
			array('/^=(.*)=/', 1),

			null
			);

		$this->multi_bracket = array(
			array(
				'open'	=> '{{{',
				'close' => '}}}',
				'multiline' => true,
				'processor' => array($this,'renderProcessor')),
			);

		$this->single_bracket = array(
			array(
				'open'	=> '{{{',
				'close' => '}}}',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '{{|',
				'close' => '|}}',
				'multiline' => false,
				'processor' => array($this,'closureProcessor')),
			array(
				'open'	=> '[[',
				'close' => ']]',
				'multiline' => false,
				'processor' => array($this,'linkProcessor')),
			array(
				'open'	=> '[',
				'close' => ']',
				'multiline' => false,
				'processor' => array($this,'macroProcessor')),

			array(
				'open'	=> '\'\'\'',
				'close' => '\'\'\'',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '\'\'',
				'close' => '\'\'',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '**',
				'close' => '**',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '~~',
				'close' => '~~',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '--',
				'close' => '--',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '__',
				'close' => '__',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '^^',
				'close' => '^^',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> ',,',
				'close' => ',,',
				'multiline' => false,
				'processor' => array($this,'textProcessor'));

		$this->macro_processors = array();
		
		$this->WikiPage = $wtext;
		$this->imageAsLink = false;
		$this->wapRender = false;

		$this->toc = array();
		$this->fn = array();
		$this->category = array();
		$this->links = array();
		$this->fn_cnt = 0;
		$this->prefix = '';
		$this->prefix = '';
		$this->included = false;
	}

	public function getLinks() {
		if(empty($this->WikiPage->title))
			return [];

		if(empty($this->links)) {
			$this->whtml = htmlspecialchars(@$this->WikiPage->text);
			$this->whtml = $this->htmlScan($this->whtml);
		}
		return $this->links;
	}

	public function toHtml() {
		// 문법을 HTML로 변환하는 함수
		if(empty($this->WikiPage->title))
			return '';
		$this->whtml = htmlspecialchars(@$this->WikiPage->text);
		$this->whtml = $this->htmlScan($this->whtml);
		return '<div pressdo-doc-paragraph>'.$this->whtml.'</div>';
	}

	private function htmlScan($text) {
		$result = '';
		$len = strlen($text);
		$now = '';
		$line = '';
		
		// 리다이렉트 from TheWiki Parser
		if(self::startsWith($text, '#') && preg_match('/^#(?:redirect|넘겨주기) (.+)$/im', $text, $target)) {
			array_push($this->links, array('target'=>$target[1], 'type'=>'redirect'));
			//@header('Location: '.$this->prefix.'/'.self::encodeURI($target[1]));
			if(defined('noredirect')){
				return '#redirect '.$target[1];
			}
			
			if(str_replace("http://thewiki.ga/w/", "", $_SERVER['HTTP_REFERER'])==str_replace("+", "%20", urlencode($target[1]))||str_replace("https://thewiki.ga/w/", "", $_SERVER['HTTP_REFERER'])==str_replace("+", "%20", urlencode($target[1]))){
				return '흐음, 잠시만요. <b>같은 문서끼리 리다이렉트 되고 있는 것 같습니다!</b><br>다음 문서중 하나를 수정하여 문제를 해결할 수 있습니다.<hr><a href="/history/'.self::encodeURI($target[1]).'" target="_blank">'.$target[1].'</a><br><a href="/history/'.str_replace("+", "%20", urlencode($_GET['w'])).'" target="_blank">'.$_GET['w'].'</a><hr>문서를 수정했는데 같은 문제가 계속 발생하나요? <a href="'.self::encodeURI($target[1]).'"><b>여기</b></a>를 확인해보세요!';
			} else {
				return 'Redirection...'.$target[1].'<script> top.location.href = "/w/'.self::encodeURI($target[1]).'"; </script>';
			}
		}
		
		// 리스트
		for($i=0;$i<$len && $i>=0;self::nextChar($text,$i)) {
			$now = self::getChar($text,$i);
			if($line == '' && $now == ' ' && $list = $this->listParser($text, $i)) {
				$result .= ''
					.$list
					.'';
				$line = '';
				$now = '';
				continue;
			}

			// 표
			if($line == '' && self::startsWith($text, '|', $i) && $table = $this->tableParser($text, $i)) {
				$result .= ''
					.$table
					.'';
				$line = '';
				$now = '';
				continue;
			}

			// 인용문
			if($line == '' && self::startsWith($text, '&gt;', $i) && $blockquote = $this->bqParser($text, $i)) {
				$result .= ''
					.$blockquote
					.'';
				$line = '';
				$now = '';
				continue;
			}

			foreach($this->multi_bracket as $bracket) {
				if(self::startsWith($text, $bracket['open'], $i) && $innerstr = $this->bracketParser($text, $i, $bracket)) {
					$result .= ''
						.$this->lineParser($line)
						.$innerstr
						.'';
					$line = '';
					$now = '';
					break;
				}
			}

			if($now == "\n") { // line parse
				$result .= $this->lineParser($line);
				$line = '';
			}
			else
				$line.=$now;
		}
		if($line != '')
			$result .= $this->lineParser($line);

		$result .= $this->printFootnote();

		// 분류 모음
		// + HTML 구조 약간 변경함
		if(!empty($this->category)) {
			$result .= '<div id="categories" pressdo-doc-category>
							<h2>분류</h2>
							<ul>';
			foreach($this->category as $category) {
				$result .= '<li pressdo-doc-category>'.$this->linkProcessor(':분류:'.$category.'|'.$category, '[[').'</li>';
			}
			$result .= '</ul></div>';
		}
		return $result;
	}

	// 인용문 from TheWiki Parser
	private function bqParser($text, &$offset) {
		$len = strlen($text);		
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
		return '<blockquote pressdo-blockquote class="wiki-quote">'.$innerhtml.'</blockquote>';
	}

	// 표
	protected function tableParser($text, &$offset) {
		$len = strlen($text);

		$tableInnerStr = '';
		$tableStyleList = array();
        $caption = '';
        for($i=$offset;$i<$len;$i=self::seekEndOfLine($text, $i)+1) {
			$now = self::getChar($text,$i);
			$eol = self::seekEndOfLine($text, $i);
			if(!self::startsWith($text, '||', $i)) {
				// table end
                break;
			}
			// || 로 시작하는 부분 처리
			$line = substr($text, $i, $eol-$i);
			$td = explode('||', $line);
			$td_cnt = count($td);

			$trInnerStr = '';
			$simpleColspan = 0;
			for($j=1;$j<$td_cnt-1;$j++) {
				$innerstr = htmlspecialchars_decode($td[$j]);

				if($innerstr=='') {
					$simpleColspan += 1;
					continue;
				} elseif(preg_match('/^\|.*?\|/', $innerstr)) {
					// 표 캡션
                    $caption_r = explode('|', $innerstr);
                    $caption = '<caption>'.$caption_r[1].'</caption>';
                    $innerstr = $caption_r[2];
                }

				$tdAttr = $tdStyleList = array();
				$trAttr = $trStyleList = array();
				
				if($simpleColspan != 0) {
					$tdAttr['colspan'] = $simpleColspan+1;
					$simpleColspan = 0;
				}
				

				// 표 스타일 적용
				while(self::startsWith($innerstr, '<') && !preg_match('/^<[^<]*?>([^<]*?)<\/.*?>/', $innerstr) && !self::startsWithi($innerstr, '<br')) {
					$dummy=0;
					$prop = $this->bracketParser($innerstr, $dummy, array('open' => '<', 'close' => '>','multiline' => false,'processor' => function($str) { return $str; }));
                    $prop = preg_replace('/^table([^ ])/', 'table $1', $prop);
                    $innerstr = substr($innerstr, $dummy+1);

                    switch($prop) {
						case '(':
							break;
						case ':':
							$tdStyleList['text-align'] = 'center';
							break;
						case ')':
							$tdStyleList['text-align'] = 'right';
							break;
						default:
							if(self::startsWith($prop, 'table ')) {
								// <table style>
								$tbprops = explode(' ', $prop);
								foreach($tbprops as $tbprop) {
									if(!preg_match('/^([^=]+)=(?|"(.*)"|\'(.*)\'|(.*))$/', $tbprop, $tbprop))
										continue;
									switch($tbprop[1]) {
										case 'align':
											switch($tbprop[2]) {
												case 'center':
													$tableStyleList['margin-left'] = 'auto';
													$tableStyleList['margin-right'] = 'auto';
													break;
												case 'right':
													$tableStyleList['float'] = 'right';
													$tableStyleList['margin-left'] = '10px';
													break;
											}
											break;
										case 'color':
											// + 글씨색 설정 추가
											$tableStyleList['color'] = $tbprop[2];
											break;
										case 'bgcolor':
											$tableStyleList['background-color'] = $tbprop[2];
											break;
										case 'bordercolor':
											$tableStyleList['border'] = '2px solid ';
											$tableStyleList['border'] .= $tbprop[2];
											break;
										case 'width':
                                            if(is_numeric($tbprop[2]))
                                                $tbprop[2] .= 'px';
											$tableStyleList['width'] = $tbprop[2];
											break;
										case 'caption':
											$caption = '<caption>'.$tbprop[2].'</caption>';
									}
								}
							}
							
							// 좌우 및 수직 정렬
							elseif(preg_match('/^(\||\-|v|\^)\|?([0-9]+)$/', $prop, $span)) {
								if($span[1] == '-') {
									$tdAttr['colspan'] = $span[2];
									break;
								}
								elseif($span[1] == '|') {
									$tdAttr['rowspan'] = $span[2];
									break;
								}
								elseif($span[1] == '^') {
									$tdAttr['rowspan'] = $span[2];
									$tdStyleList['vertical-align'] = 'top';
									break;
								}
								elseif($span[1] == 'v') {
									$tdAttr['rowspan'] = $span[2];
									$tdStyleList['vertical-align'] = 'bottom';
									break;
								}
							}
							elseif(preg_match('/^#(?:([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})|([A-Za-z]+))$/', $prop, $span)) {
								// 표 배경색
								$tdStyleList['background-color'] = $span[1]?'#'.$span[1]:$span[2];
								break;
							}
							elseif(preg_match('/^([^=]+)=(?|"(.*)"|\'(.*)\'|(.*))$/', $prop, $match)) {
								switch($match[1]) {
									// + td, tr 글씨색 설정 추가
									case 'color':
										$tdStyleList['color'] = $match[2];
										break;
									case 'rowcolor':
										$trStyleList['color'] = $match[2];
										break;
									case 'bgcolor':
										$tdStyleList['background-color'] = $match[2];
										break;
									case 'rowbgcolor':
										$trStyleList['background-color'] = $match[2];
                                        break;
									case 'width':
										$tdStyleList['width'] = $match[2];
										break;
									case 'height':
										$tdStyleList['height'] = $match[2];
										break;
								}
							}
                            else
                                $tdStyleList['background-color'] = $prop;
					}
				}

                if(empty($tdStyleList['text-align'])) {
					// 표 텍스트정렬
                    if(self::startsWith($innerstr, ' ') && self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = 'center';
                    elseif(self::startsWith($innerstr, ' ') && !self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = 'right';
                    elseif(!self::startsWith($innerstr, ' ') && self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = 'left';
                    else
                        $tdStyleList['text-align'] = null;
                }

                $innerstr = trim($innerstr);

				$tdAttr['style'] = '';
				foreach($tdStyleList as $styleName =>$tdstyleValue) {
					if(empty($tdstyleValue))
						continue;
					$tdAttr['style'] .= $styleName.': '.$tdstyleValue.'; ';
				}
				
				$trAttr['style'] = '';
				foreach($trStyleList as $styleName =>$trstyleValue) {
					if(empty($trstyleValue))
						continue;
					$trAttr['style'] .= $styleName.': '.$trstyleValue.'; ';
				}

				$tdAttrStr = '';
				foreach($tdAttr as $propName => $propValue) {
					if(empty($propValue))
						continue;
					$tdAttrStr .= ' '.$propName.'="'.str_replace('"', '\\"', $propValue).'"';
				}
				
				if (!isset($trAttrStri)) {
					$trAttrStri = true;
					$trAttrStr = '';
					foreach($trAttr as $propName => $propValue) {
						if(empty($propValue))
							continue;
						$trAttrStr .= ' '.$propName.'="'.str_replace('"', '\\"', $propValue).'"';
					}
				}
				$trInnerStr .= '<td'.$tdAttrStr.'><div class="wiki-paragraph">'.$this->blockParser($innerstr).'</div></td>';
			}
			$tableInnerStr .= !empty($trInnerStr)?'<tr'.$trAttrStr.'>'.$trInnerStr.'</tr>':'';
			unset($trAttrStri);
		}

		if(empty($tableInnerStr))
			return false;

		$tableStyleStr = '';
		foreach($tableStyleList as $styleName =>$styleValue) {
			if(empty($styleValue))
				continue;
			$tableStyleStr .= $styleName.': '.$styleValue.'; ';
		}

		// HTML <table> 태그 생성
		$tableAttrStr = ($tableStyleStr?' style="'.$tableStyleStr.'"':'');
		$result = '<div pressdo-doc-tablewrap><table pressdo-doc-table'.$tableAttrStr.'>'.$caption.$tableInnerStr."</table></div>\n";
		$offset = $i-1;
		return $result;
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

		$last = count($arr)-1;
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
		// + 공백 있어서 안 되는 오류 수정
		if(self::startsWith($line, '=') && preg_match('/^(=+)(.*?)(=+) *$/', trim($line), $match) && $match[1]===$match[3]) {	
			$level = strlen($match[1]);
			$innertext = $this->blockParser($match[2]);

			// + 접힌문단 기능 추가
			    if (preg_match('/^# (.*) #$/', $innertext, $ftoc)) {
						$folded = 'pressdo-toc-fold="fold"';
						$innertext = $ftoc[1];
			    }else{
				$folded = 'pressdo-toc-fold="show"';
			    }
			$id = $this->tocInsert($this->toc, $innertext, $level);
			$js = 'onclick="hiddencontents(\'s-'.$id.'\')"';
			$result .= '</div><h'.$level.' pressdo-toc '.$js.' '.$folded.' id="s-'.$id.'">
						<a name="s-'.$id.'" href="#_toc">'.$id.'. </a>';

			// + 문단에서 앵커가 태그속에 들어가는 부분 수정
			if(preg_match('/\[anchor\((.*)\)\]/', $innertext, $anchor)){
				$RealContent = str_replace($anchor[0], '', $innertext);
				$result .= '<a id="'.$anchor[1].'"></a><span id="'.$RealContent.'">'.$RealContent;
			}else{
				$result .= '<span id="'.$innertext.'">'.$innertext;
			}

			// + 부분 편집 기능 작업
			// + content-s- 속성 추가 (문단 숨기기용)
			$result .= '<span pressdo-edit-section><a href="/edit/@@@PressDo-Replace-Title-Here@@@?section='.$id.'" rel="nofollow">[편집]</a></span>
							</span>
						</h'.$level.'><div id="content-s-'.$id.'" pressdo-doc-paragraph '.$folded.'>';
			$line = '';
			
		}
		
              $line = preg_replace('/^[^-]*(-{4,9})[^-]*$/', '<hr>', $line);

		$line = $this->blockParser($line);

		if($line != '') {
			if($this->wapRender)
				$result .= $line.'<br/><br/>';
			else
				$result .= $line.'<br/>';
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
			// 외부이미지
			if(self::startsWith($line, 'http', $j) && preg_match('/(https?:\/\/[^ ]+\.(jpg|jpeg|png|gif))(?:\?([^ ]+))?/i', $line, $match, 0, $j)) {
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
			}elseif(self::startsWith($line, 'attachment', $j) && preg_match('/attachment:([^\/]*\/)?([^ ]+\.(?:jpg|jpeg|png|gif))(?:\?([^ ]+))?/i', $line, $match, 0, $j)) {
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
				// from TheWiki Parser
				if(substr($line, 4, 6)=='#!wiki'){
					return '<div '.htmlspecialchars_decode(substr($line, 11)).'>_(#!WIKIMARK)_';
				}
				if(preg_match('/^{{{#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3}) (.*)$/', $line, $match)) {
					if(count(explode("}}}", $match[0]))<=1){
						$this->color_temp_line[] = $line;
						$this->color = $match[1];
						$this->finded_color_line = true;
						return;
					}
				}
				if(count(explode("}}}", $line))>1&&count($this->color_temp_line)>0&&$this->finded_color_line) {
					$line = implode("}}}{{{#!html <br>}}}{{{#".$this->color." ", $this->color_temp_line)." }}}{{{#!html <br>}}}{{{#".$this->color." ".$line;
					unset($this->color_temp_line);
					$this->finded_color_line = false;
				}
				if($this->finded_color_line){
					$this->color_temp_line[] = $line;
					return;
				}
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

	// TheWiki Parser 코드 적용
	private function renderProcessor($text, $type) {
       		if(self::startsWithi($text, '#!html')) {
			$html = substr($text, 6);
			$html = ltrim($html);
			$html = htmlspecialchars_decode($html);
			$html = self::inlineHtml($html);
			return $html;
		}
		if(self::startsWithi($text, '#!wiki') && preg_match('/([^\n]*)\n(((((.*)(\n)?)+)))/', substr($text, 7), $match)) {
			$wPage2 = new PlainWikiPage($match[2]);
			$child = new NamuMark($wPage2);
			$child->prefix = $this->prefix;
			$child->imageAsLink = $this->imageAsLink;
			$child->wapRender = $this->wapRender;
			$child->included = true;
			$twPrint = $child->toHtml();
			
			return '<div pressdo-wikistyle '.htmlspecialchars_decode($match[1]).'>'.htmlspecialchars_decode($twPrint).'</div>';
		}
		return '<pre><code>'.substr($text, 1).'</code></pre>';
	}

	private function closureProcessor($text, $type) {
		return '<div pressdo-wiki-closure>'.$this->formatParser($text).'</div>';
	}

	private function linkProcessor($text, $type) {
		$href = explode('|', $text);
		if(preg_match('/^https?:\/\//', $href[0])) {
			// [[URL]]
			$targetUrl = $href[0];
			$class = '_extlink';
			$target = '_blank';
			$datanm = 'pressdo-link-external';
		}elseif(preg_match('/^분류:(.+)$/', $href[0], $category)) {
			// [[분류:분류]]
			array_push($this->links, array('target'=>$category[0], 'type'=>'category'));
			if(!$this->included)
				array_push($this->category, $category[1]);
			return ' ';
		}elseif(preg_match('/^#(.+)$/', $href[0], $category)) {
			// [[#anchor]]
			$targetUrl = $href[0];
			return ' ';
		}elseif(preg_match('/^파일:(.+)$/', $href[0], $category)) {
			// [[파일:ㅁㅁ]]
			array_push($this->links, array('target'=>$category[0], 'type'=>'file'));
			if($this->imageAsLink)
				return '<span pressdo-link-file class="alternative">[<a pressdo-link-file target="_blank" title="'.$category[0].'" href="'.self::encodeURI($category[0]).'">image</a>]</span>';
			
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
			$xd = md5($category[0].rand(1,50));
			$ext = strtolower(end(explode(".", $category[0])));
			$hash = sha1($category[0], FALSE);
			
			if(is_file("../files/".$hash.".".$ext)){
				$google_photos_check = fopen("../files/".$hash.".".$ext, "r");
				$google_photos = fread($google_photos_check, 158);
				fclose($google_photos_check);
				if(substr($google_photos, 0, 4)=="http"){
					return '<img src="'.$google_photos.'" '.trim(str_replace('style="', 'style="cursor:hand; ', $paramtxt)).'>';
				} else {
					return '<img src="/files/'.$hash.'.'.$ext.'" '.trim(str_replace('style="', 'style="cursor:hand; ', $paramtxt)).'>';
				}
			}
			
			$img = "SELECT * FROM file WHERE name = binary('$category[0]') LIMIT 1";
			$imgres = mysqli_query($config_db, $img);
			$imgarr = mysqli_fetch_array($imgres);
			mysqli_close($conn);
			if($imgarr['google']!=""){
				return '<img src="'.$imgarr['google'].'" '.trim(str_replace('style="', 'style="cursor:hand; ', $paramtxt)).'>';
			} else if($imgarr['dir']!=""){
				//return '[ No.'.$imgarr['no'].' ] 이미지 작업 대기중';
				return '<img src="//images.thewiki.ga/'.$imgarr['dir'].'" '.trim(str_replace('style="', 'style="cursor:hand; ', $paramtxt)).'>';
			} else {
				return '<script type="text/javascript"> $(document).ready(function(){ $.post("//thewiki.ga/API.php", {w:"'.$category[0].'", p:"'.str_replace('"', '\"', $paramtxt).'"}, function(data){ $("#ajax_file_'.$xd.'").html(data); $("#ajax_file_'.$xd.'").prepend("<input type=\'hidden\' id=\'enableajax_'.$xd.'\' value=\'false\'>"); $("#ajax_file_'.$xd.' > img").unwrap(); }, "html"); }); </script><div id="ajax_file_'.$xd.'" style="z-index:-1;"><table class="wiki-table" style=""><tbody><tr><td style="background-color:#93C572; text-align:center;"><p><span class="wiki-size size-1"><font color="006400">'.$category[0].' 이미지 표시중</font></span></p></td></tr></tbody></table></div>';
			}
		}
		elseif(preg_match('/^이미지:(.+)$/', $href[0], $category)) {
			array_push($this->links, array('target'=>$category[0], 'type'=>'file'));
			if($this->imageAsLink)
				return '<span pressdo-img-alternative class="alternative">[<a target="_blank" href="'.self::encodeURI($category[0]).'">image</a>]</span>';
			
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
			return '<img pressdo-img-custom src="/customupload/'.mb_substr($category[0], 4, strlen($category[0]), "UTF-8").'" '.trim(str_replace('style="', 'style="cursor:hand; ', $paramtxt)).'>';
		}
		else {
			if(self::startsWith($href[0], ':')) {
				$href[0] = substr($href[0], 1);
				$c=1;
			}
			$targetUrl = $this->prefix.'/'.self::encodeURI($href[0]);
			if($this->wapRender && !empty($href[1]))
				$title = $href[0];
			if(empty($c))
				array_push($this->links, array('target'=>$href[0], 'type'=>'link'));
		}
		return '<a pressdo-link href="'.$targetUrl.'"'.(!empty($datanm)?" $datanm ":'').(!empty($title)?' title="'.$title.'"':'').(!empty($class)?' class="'.$class.'"':'').(!empty($target)?' target="'.$target.'"':'').'>'.(!empty($href[1])?$this->formatParser($href[1]):$href[0]).'</a>';
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
				// [date]
				return date('Y-m-d H:i:s');
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
			default:
				if(self::startsWithi(strtolower($text), 'include') && preg_match('/^include\((.+)\)$/i', $text, $include) && $include = $include[1]) {
					if($this->included)
						return ' ';
					$include = explode(',', $include);
					array_push($this->links, array('target'=>$include[0], 'type'=>'include'));
					
					$w = $include[0];
					if(count(explode(":", $w))>1){
						$tp = explode(":", $w);
						switch($tp[0]){
							case '틀':
								$namespace = '1';
								break;
							case '분류':
								$namespace = '2';
								break;
							case '파일':
								$namespace = '3';
								break;
							case '사용자':
								$namespace = '4';
								break;
							case '나무위키':
								$namespace = '6';
								break;
							case '휴지통':
								$namespace = '8';
								break;
							case 'TheWiki':
								$namespace = '10';
								break;
							case '이미지':
								$namespace = '11';
								break;
							default:
								$namespace = '0';
						
						}
						if($namespace>0){
							$w = str_replace($tp[0].":", "", implode(":", $tp));
						}
					}
					$_POST = array('namespace'=>$namespace, 'title'=>$w, 'ip'=>$_SERVER['REMOTE_ADDR'], 'option'=>'original');
					include $_SERVER['DOCUMENT_ROOT'].'/API.php';
					
					if($api_result->status!='success'||$api_result->type=='refresh'){
						return ' ';
					} else {
						$arr['text'] = $api_result->data;
						unset($api_result);
					}
					
					if(defined("isdeleted")){
						return ' ';
					}
					
					// themark 통합
					$arr['text'] = simplemark($arr['text']);
					
					// #!folding 문법 #!end}}} 치환
					$foldingstart = explode('{{{#!folding ', $arr['text']);
					for($z=1;$z<count($foldingstart);$z++){
						$foldingcheck = true;
						$find = '';
						$match = '';
						$temp_explode = '';
						
						if(count(explode("}}}", $foldingstart[$z]))>1){
							$temp_explode = explode("}}}", $foldingstart[$z]);
							
							$end_loop = 0;
							while(count($temp_explode)>$end_loop){
								if(count(explode('{{{', $temp_explode[$end_loop]))>1){
									$end_loop++;
								} else {
									for($x=0;$end_loop>$x;$x++){
										$match .= $temp_explode[$x].'}}}';
									}
									$find = $match.$temp_explode[$end_loop].'}}}';
									$match .= $temp_explode[$end_loop].'#!end}}}';
									$end_loop = count($temp_explode)+1;
								}
							}
							
							$arr['text'] = str_replace('{{{#!folding '.$find, '{{{#!folding '.$match, $arr['text']);
						}
					}
					// #!folding 문법 우선 적용
					$foldingstart = explode('{{{#!folding ', $arr['text']);
					for($z=1;$z<count($foldingstart);$z++){
						$foldingcheck = true;
						$foldopentemp = reset(explode("\n", $foldingstart[$z]));
						if(count(explode("#!end}}}", $foldingstart[$z]))>1){
							$foldingtemp = str_replace("#!end}}}", "_(FOLDINGEND)_", $foldingstart[$z]);
							$foldingdatatemp = next(explode($foldopentemp, reset(explode("_(FOLDINGEND)_", $foldingtemp))));
							$md5 = md5(rand(1,10).$foldingdatatemp);
							$foldopen[$md5] = $foldopentemp;
							$foldingdata[$md5] = $foldingdatatemp;
							$arr['text'] = str_replace("{{{#!folding ".$foldopentemp.$foldingdatatemp."#!end}}}", "_(FOLDINGSTART)_".$md5."_(FOLDINGSTART2)_ _(FOLDINGDATA)_".$md5."_(FOLDINGDATA2)_ _(FOLDINGEND)_", $arr['text']);
						}
					}
					
					if($arr['text']!="") {
						foreach($include as $var) {
							$var = explode('=', ltrim($var));
							if(empty($var[1]))
								$var[1]='';
							$arr['text'] = str_replace('@'.$var[0].'@', $var[1], $arr['text']);
						}
						
						$wPage2 = new PlainWikiPage($arr['text']);
						$child = new NamuMark($wPage2);
						$child->prefix = $this->prefix;
						$child->imageAsLink = $this->imageAsLink;
						$child->wapRender = $this->wapRender;
						$child->included = true;
						$twPrint = $child->toHtml();
						
						// #!folding
						if($foldingcheck){
							$twPrint = str_replace('_(FOLDINGEND)_', '</div></dd></dl>', $twPrint);
							
							$getmd5 = explode("_(FOLDINGDATA)_", $twPrint);
							for($xz=1;$xz<count($getmd5);$xz++){
								$mymd5 = reset(explode("_(FOLDINGDATA2)_", $getmd5[$xz]));
								$twPrint = str_replace('_(FOLDINGSTART)_'.$mymd5.'_(FOLDINGSTART2)_', '<dl class="wiki-folding"><dt><center>'.$foldopen[$mymd5].'</center></dt><dd style="display: none;"><div class="wiki-table-wrap">', $twPrint);
								
								$fPage = new PlainWikiPage($foldingdata[$mymd5]);
								$child = new NamuMark($fPage);
								$child->prefix = $this->prefix;
								$child->imageAsLink = $this->imageAsLink;
								$child->wapRender = $this->wapRender;
								$child->included = true;
								$fwPrint = $child->toHtml();
								
								$twPrint = str_replace('<div class="wiki-table-wrap"> _(FOLDINGDATA)_'.$mymd5.'_(FOLDINGDATA2)_ </div>', '<div class="wiki-table-wrap"> '.$fwPrint.' </div>', $twPrint);
							}
						}
						
						return $twPrint;
					}
					return ' ';
				}
				elseif(self::startsWith(strtolower($text), 'youtube') && preg_match('/^youtube\((.+)\)$/i', $text, $include) && $include = $include[1]) {
					// 유튜브 동영상
					$include = explode(',', $include);
					$var = array();
					foreach($include as $v) {
						$v = explode('=', $v);
						if(empty($v[1]))
							$v[1]='';
						$var[$v[0]] = $v[1];
					}
					return '<iframe width="'.(!empty($var['width'])?$var['width']:'640').'" height="'.(!empty($var['height'])?$var['height']:'360').'" src="//www.youtube.com/embed/'.$include[0].'" frameborder="0" allowfullscreen></iframe>';
				}
				elseif(self::startsWith(strtolower($text), 'nicovideo') && preg_match('/^nicovideo\((.+)\)$/i', $text, $include) && $include = $include[1]) {
					// 니코 동영상
					$include = explode(',', $include);
					$var = array();
					foreach($include as $v) {
						$v = explode('=', $v);
						if(empty($v[1]))
							$v[1]='';
						$var[$v[0]] = $v[1];
					}
					return '<iframe width="'.(!empty($var['width'])?$var['width']:'640').'" height="'.(!empty($var['height'])?$var['height']:'360').'" src="http://ext.nicovideo.jp/thumb_watch/'.$include[0].'?w='.(!empty($var['width'])?$var['width']:'640').'&h='.(!empty($var['height'])?$var['height']:'360').'" frameborder="0" allowfullscreen></iframe>';
				}
				elseif(self::startsWith(strtolower($text), 'kakaotv') && preg_match('/^kakaotv\((.+)\)$/', $text, $include) && $include = $include[1]) {
					// 카카오 동영상
					$include = explode(',', $include);
					$var = array();
					foreach($include as $v) {
						$v = explode('=', $v);
						if(empty($v[1]))
							$v[1]='';
						$var[$v[0]] = $v[1];
					}
					return '<iframe width="'.(!empty($var['width'])?$var['width']:'640').'" height="'.(!empty($var['height'])?$var['height']:'360').'" src="https://play-tv.kakao.com/embed/player/cliplink/'.$include[0].'?service=player_share" frameborder="0" allowfullscreen></iframe>';
				}
				elseif(self::startsWith($text, 'video') && preg_match('/^video\((.+)\)$/', $text, $include) && $include = $include[1]) {
					// + 커스텀 동영상: [video(url)]
					$include = explode(',', $include);
					$var = array();
					foreach($include as $v) {
						$v = explode('=', $v);
						if(empty($v[1]))
							$v[1]='';
						$var[$v[0]] = $v[1];
					}
					return '<iframe width="'.(!empty($var['width'])?$var['width']:'640').'" height="'.(!empty($var['height'])?$var['height']:'360').'" src="'.$include[0].'" frameborder="0" allowfullscreen></iframe>';
				}
				elseif(self::startsWithi(strtolower($text), 'age') && preg_match('/^age\((.+)\)$/i', $text, $include) && $include = $include[1]) {
					// 연령
					$include = explode('-', $include);
					$age = (date("md", date("U", mktime(0, 0, 0, $include[1], $include[2], $include[0]))) > date("md")
						? ((date("Y") - $include[0]) - 1)
						: (date("Y") - $include[0]));
					return $age;
					
				}
				elseif(self::startsWithi(strtolower($text), 'anchor') && preg_match('/^anchor\((.+)\)$/i', $text, $include) && $include = $include[1]) {
					// 앵커
					return '<a name="'.$include.'"></a>';
				}
				elseif(self::startsWithi(strtolower($text), 'dday') && preg_match('/^dday\((.+)\)$/i', $text, $include) && $include = $include[1]) {
					// D-DAY
					$nDate = date("Y-m-d", time());
					if(strtotime($nDate)==strtotime($include)){
						return " 0";
					}
					return intval((strtotime($nDate)-strtotime($include)) / 86400);
				}
				elseif(self::startsWith($text, '*') && preg_match('/^\*([^ ]*)([ ].+)?$/', $text, $note)) {
					// 각주
					$notetext = !empty($note[2])?$this->blockParser($note[2]):'';
					$id = $this->fnInsert($this->fn, $notetext, $note[1]);
					$preview = $notetext;
					$preview2 = strip_tags($preview, '<img>');
					$preview = strip_tags($preview);
					$preview = str_replace('"', '\\"', $preview);
					return '<a id="rfn-'.htmlspecialchars($id).'" class="wiki-fn" href="#fn-'.rawurlencode($id).'" title="'.$preview.'">['.($note[1]?$note[1]:$id).']</a>';
				}
		}
		return '['.$text.']';
	}

	// TheWiki Parser 일부 
	private function textProcessor($otext, $type) {
		if($type != '{{{')
			$text = $this->formatParser($otext);
		else
			$text = $otext;
        	switch ($type) {
			case '\'\'\'':
				// 볼드체
                		return '<strong>'.$text.'</strong>';
			case '\'\'':
				// 기울임꼴
				return '<em>'.$text.'</em>';
			case '**':
				// + ** 문법 추가
				return '<strong>'.$text.'</strong>';
            		case '--':
			case '~~':
				// 취소선
				// + 수평선 적용 안 되는 오류 수정
               			if($this->strikeLine){
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
				if(self::startsWith($text, '#!html')) {
					$html = substr($text, 6);
					$html = ltrim($html);
					$html = htmlspecialchars_decode($html);
					$html = self::inlineHtml($html);
#					echo $html;
					return $html;
				}
				if(preg_match('/^#(?:([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})|([A-Za-z]+)) (.*)$/', $text, $color)) {
					if(empty($color[1]) && empty($color[2]))
						return $text;
					return '<span style="color: '.(empty($color[1])?$color[2]:'#'.$color[1]).'">'.$this->formatParser($color[3]).'</span>';
				}
				if(preg_match('/^\+([1-5]) (.*)$/', $text, $size)) {
					return '<span class="wiki-size size-'.$size[1].'">'.$this->formatParser($size[2]).'</span>';
				}
					// 문법 이스케이프
                    		return '<code pressdo-nowiki>' . $text . '</code>';
                }
                return $type.$text.$type;
	}

	// 각주 삽입
	private function fnInsert(&$arr, &$text, $id = null) {
		$arr_cnt = count($arr);
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
		if(count($this->fn)==0)
			return '';

		$result = $this->wapRender?'<hr>':'<hr><ol class="fn">';
		foreach($this->fn as $k => $fn) {
			$result .= $this->wapRender?'<p>':'<span>';
			if($fn['count']>1) {
				$result .= '['.$fn['id'].'] ';
				for($i=0;$i<$fn['count'];$i++) {
					$result .= '<a id="fn-'.htmlspecialchars($fn['id']).'-'.($i+1).'" href="#rfn-'.rawurlencode($fn['id']).'-'.($i+1).'">'.chr(ord('A') + $i).'</a> ';
				}
			}
			else {
				$result .= '<a id="fn-'.htmlspecialchars($fn['id']).'" href="#rfn-'.$fn['id'].'">['.$fn['id'].']</a> ';
			}
			$result .= $this->blockParser($fn['text'])
								.($this->wapRender?'</p>':'</span><br>');
		}
		$result .= $this->wapRender?'':'</ol>';
		$this->fn = array();
		return $result;
	}

	// 목차 삽입
	private function tocInsert(&$arr, $text, $level, $path = '') {
		if(empty($arr[0])) {
			$arr[0] = array('name' => $text, 'level' => $level, 'childNodes' => array());
			return $path.'1';
		}
		$last = count($arr)-1;
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

	function encodeURI($str) {
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
