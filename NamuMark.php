<?php
//include 'config.php';
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
 */

class PlainWikiPage {
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

		$this->list_tag = array(
			array('*', 'ul'),
			array('1.', 'ol class="decimal"'),
			array('A.', 'ol class="upper-alpha"'),
			array('a.', 'ol class="lower-alpha"'),
			array('I.', 'ol class="upper-roman"'),
			array('i.', 'ol class="lower-roman"')
			);

		$this->h_tag = array(
			array('/^====== (.*) ======/', 6),
			array('/^===== (.*) =====/', 5),
			array('/^==== (.*) ====/', 4),
			array('/^=== (.*) ===/', 3),
			array('/^== (.*) ==/', 2),
			array('/^= (.*) =/', 1),

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
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '$ ',
				'close' => ' $',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '<!--',
				'close' => '-->',
				'multiline' => false,
				'processor' => array($this,'textProcessor')),
			array(
				'open'	=> '<nowiki>',
				'close' => '</nowiki>',
				'multiline' => false,
				'processor' => array($this,'textProcessor'))
			);

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
		if(empty($this->WikiPage->title))
			return '';
		$this->whtml = htmlspecialchars(@$this->WikiPage->text);
		$this->whtml = $this->htmlScan($this->whtml);
		return $this->whtml;
	}

	private function htmlScan($text) {
		$result = '';
		$len = strlen($text);
		$now = '';
		$line = '';

		if(self::startsWith($text, '#') && preg_match('/^#(?:redirect|넘겨주기) (.+)$/im', $text, $target)) {
			array_push($this->links, array('target'=>$target[1], 'type'=>'redirect'));
			@header('Location: '.$this->prefix.'/'.self::encodeURI($target[1]));
			return;
		}

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

			if($line == '' && self::startsWith($text, '|', $i) && $table = $this->tableParser($text, $i)) {
				$result .= ''
					.$table
					.'';
				$line = '';
				$now = '';
				continue;
			}

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

		if(!empty($this->category)) {
			$result .= '<div class="wiki-category"><h2>분류</h2><ul>';
			foreach($this->category as $category) {
				$result .= '<li>'.$this->linkProcessor(':분류:'.$category.'|'.$category, '[[').'</li>';
			}
			$result .= '</div>';
		}
		return $result;
	}

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
		return '<blockquote>'.$innerhtml.'</blockquote>';
	}

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
				


				while(self::startsWith($innerstr, '<') && !preg_match('/^<[^<]*?>([^<]*?)<\/.*?>/', $innerstr) && !self::startsWithi($innerstr, '<br')) {
					$dummy=0;
					$prop = $this->bracketParser($innerstr, $dummy, array('open'	=> '<', 'close' => '>','multiline' => false,'processor' => function($str) { return $str; }));
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
								$tdStyleList['background-color'] = $span[1]?'#'.$span[1]:$span[2];
								break;
							}
							elseif(preg_match('/^([^=]+)=(?|"(.*)"|\'(.*)\'|(.*))$/', $prop, $match)) {
								switch($match[1]) {
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
                    if(self::startsWith($innerstr, ' ') && self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = 'center';
                    elseif(self::startsWith($innerstr, ' ') && !self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = null;
                    elseif(!self::startsWith($innerstr, ' ') && self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = 'right';
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
				$trInnerStr .= '<td'.$tdAttrStr.'>'.$this->blockParser($innerstr).'</td>';
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

		$tableAttrStr = ($tableStyleStr?' style="'.$tableStyleStr.'"':'');
		$result = '<table class="wikitable"'.$tableAttrStr.'>'.$caption.$tableInnerStr."</table>\n";
		$offset = $i-1;
		return $result;
	}

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

		// comment
		if(self::startsWith($line, '##')) {
			$line = '';
		}

		// == Title ==
		if(self::startsWith($line, '=') && preg_match('/^(=+) (.*) (=+)[ ]*$/', $line, $match) && $match[1]===$match[3]) {
			$level = strlen($match[1]);
			$innertext = $this->blockParser($match[2]);
			$id = $this->tocInsert($this->toc, $innertext, $level);
			$result .= '<h'.$level.' id="s-'.$id.'"><a name="s-'.$id.'" href="#toc">'.$id.'</a>. '.$innertext.'</h'.$level.'>';
			$line = '';
		}

		// hr
		if($line == '----') {
			$result .= '<hr>';
			$line = '';
		}

		$line = $this->blockParser($line);

		if($line != '') {
			if($this->wapRender)
				$result .= $line.'<br/><br/>';
			else
				$result .= '<p>'.$line.'</p>';
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
			if(self::startsWith($line, 'http', $j) && preg_match('/(https?:\/\/[^ ]+\.(jpg|jpeg|png|gif))(?:\?([^ ]+))?/i', $line, $match, 0, $j)) {
				if($this->imageAsLink)
					$innerstr = '<span class="alternative">[<a class="external" target="_blank" href="'.$match[1].'">image</a>]</span>';
				else {
					$paramtxt = '';
					$csstxt = '';
					if(!empty($match[3])) {
						preg_match_all('/[&?]?([^=]+)=([^\&]+)/', htmlspecialchars_decode($match[3]), $param, PREG_SET_ORDER);
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
					$innerstr = '<img src="'.$match[1].'"'.$paramtxt.'>';
				}
				$line = substr($line, 0, $j).$innerstr.substr($line, $j+strlen($match[0]));
				$line_len = strlen($line);
				$j+=strlen($innerstr)-1;
				continue;
			}elseif(self::startsWith($line, 'attachment', $j) && preg_match('/attachment:([^\/]*\/)?([^ ]+\.(?:jpg|jpeg|png|gif))(?:\?([^ ]+))?/i', $line, $match, 0, $j)) {
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
        if($type == '{{|') {
            if(preg_match('/\|-/', $text))
                return $type.$text.$type;
            else
                return '<poem style="border: 2px solid #d6d2c5; background-color: #f9f4e6; padding: 1em;">'.$text.'</poem>';
        } else {
            $lines = explode("\n", $text);
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

            if(self::startsWithi($text, '#!html')) {
                return '<html>' . preg_replace('/UNIQ--.*?--QINU/', '', substr($text, 7)) . '</html>';
            } elseif(self::startsWithi($text, '#!wiki') && preg_match('/([^\n]*)\n(((((.*)(\n)?)+)))/', substr($text, 7), $match)) {
                return '<div '.$match[1].'>'.$match[2].'</div>';
            } elseif(self::startsWithi($text, '#!syntax') && preg_match('/#!syntax ([^\s]*)/', $text, $match)) {
                return '<syntaxhighlight lang="' . $match[1] . '" line="1">' . preg_replace('/#!syntax ([^\s]*)/', '', $text) . '</syntaxhighlight>';
            } elseif(preg_match('/^\+([1-5])(.*)$/sm', $text, $size)) {
                for ($i=1; $i<=$size[1]; $i++){
                    if(isset($big_before) && isset($big_after)) {
                        $big_before .= '<big>';
                        $big_after .= '</big>';
                    } else {
                        $big_before = '<big>';
                        $big_after = '</big>';
                    }
                }

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

                return $big_before.$this->formatParser($size[2]).$big_after;
            } elseif(preg_match('/^\-([1-5])(.*)$/sm', $text, $size)) {
                for ($i = 1; $i <= $size[1]; $i++) {
                    if (isset($small_before) && isset($small_after)) {
                        $small_before .= '<small>';
                        $small_after .= '</small>';
                    } else {
                        $small_before = '<small>';
                        $small_after = '</small>';
                    }
                }

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

                return $small_before . $this->formatParser($size[2]) . $small_after;
            } else {
                return '<pre class="_nowiki">' . $text . '</pre>';
            }
        }
	}

	private function closureProcessor($text, $type) {
		return '<div class="wiki-closure">'.$this->formatParser($text).'</div>';
	}

	private function linkProcessor($text, $type) {
		$href = explode('|', $text);
		if(preg_match('/^https?:\/\//', $href[0])) {
			$targetUrl = $href[0];
			$class = 'externalLink unnamed external';
			$target = '_blank';
		}elseif(preg_match('/^분류:(.+)$/', $href[0], $category)) {
			array_push($this->links, array('target'=>$category[0], 'type'=>'category'));
			if(!$this->included)
				array_push($this->category, $category[1]);
			return ' ';
		}elseif(preg_match('/^파일:(.+)$/', $href[0], $category)) {
			array_push($this->links, array('target'=>$category[0], 'type'=>'file'));
			if($this->imageAsLink)
				return '<span class="alternative">[<a target="_blank" href="'.self::encodeURI($category[0]).'">image</a>]</span>';
			
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
			return '<a href="'.$this->prefix.'/'.self::encodeURI($category[0]).'" title="'.htmlspecialchars($category[0]).'"><img src="https://namu.wiki/file/'.self::encodeURI($category[0]).'"'.$paramtxt.'></a>';
		}else {
			if(self::startsWith($href[0], ':')) {
				$href[0] = substr($href[0], 1);
				$c=1;
			}
			$targetUrl = $this->prefix.self::encodeURI($href[0]);
			if($this->wapRender && !empty($href[1]))
				$title = $href[0];
			if(empty($c))
				array_push($this->links, array('target'=>$href[0], 'type'=>'link'));
		}
		return '<a href="'.$targetUrl.'"'.(!empty($title)?' title="'.$title.'"':'').(!empty($class)?' class="'.$class.'"':'').(!empty($target)?' target="'.$target.'"':'').'>'.(!empty($href[1])?$this->formatParser($href[1]):$href[0]).'</a>';
	}

	private function macroProcessor($text, $type) {
		$macroName = strtolower($text);
		if(!empty($this->macro_processors[$macroName]))
			return $this->macro_processors[$macroName]();
		switch($macroName) {
			case 'br':
				return '<br>';
			case 'date':
				return date('Y-m-d H:i:s');
			case '목차':
			case 'tableofcontents':
				return $this->printToc();
			case '각주':
			case 'footnote':
				return $this->printFootnote();
			default:
				if(self::startsWithi($text, 'include') && preg_match('/^include\((.+)\)$/i', $text, $include) && $include = $include[1]) {
					if($this->included)
						return ' ';

					$include = explode(',', $include);
					array_push($this->links, array('target'=>$include[0], 'type'=>'include'));
					if(($page = $this->WikiPage->getPage($include[0])) && !empty($page->text)) {
						foreach($include as $var) {
							$var = explode('=', ltrim($var));
							if(empty($var[1]))
								$var[1]='';
							$page->text = str_replace('@'.$var[0].'@', $var[1], $page->text);
						}
						$child = new NamuMark($page);
						$child->prefix = $this->prefix;
						$child->imageAsLink = $this->imageAsLink;
						$child->wapRender = $this->wapRender;
						$child->included = true;
						return $child->toHtml();
					}
					return ' ';
				}
				elseif(self::startsWith($text, 'youtube') && preg_match('/^youtube\((.+)\)$/', $text, $include) && $include = $include[1]) {
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
				elseif(self::startsWith($text, 'nicovideo') && preg_match('/^nicovideo\((.+)\)$/', $text, $include) && $include = $include[1]) {
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
				elseif(self::startsWith($text, '*') && preg_match('/^\*([^ ]*)([ ].+)?$/', $text, $note)) {
					$notetext = !empty($note[2])?$this->blockParser($note[2]):'';
					$id = $this->fnInsert($this->fn, $notetext, $note[1]);
					$preview = $notetext;
					$preview = strip_tags($preview);
					$preview = str_replace('"', '\\"', $preview);
					return '<a id="rfn-'.htmlspecialchars($id).'" class="wiki-fn" href="#fn-'.rawurlencode($id).'" title="'.$preview.'">['.($note[1]?$note[1]:$id).']</a>';
				}
		}
		return '['.$text.']';
	}

	private function textProcessor($otext, $type) {
		if($type != '{{{' && $type != '<nowiki>')
			$text = $this->formatParser($otext);
		else
			$text = $otext;
        switch ($type) {
            case '\'\'\'':
                return '<strong>'.$text.'</strong>';
            case '\'\'':
                return '<em>'.$text.'</em>';
            case '--':
            case '~~':
                if (!self::startsWith($text, 'item-') && !self::endsWith($text, 'UNIQ') && !self::startsWith($text, 'QINU') && !preg_match('/^.*?-.*-QINU/', $text) && !self::startsWith($text, 'h-')) {
                    return '<s>'.$text.'</s>';
                } else {
                    return $type.$text.$type;
                }
                    // no break
            case '__':
                if (preg_match('/TOC/', $text) || preg_match('/^.*?(\.jpeg|\.jpg|\.png|\.gif)/', $text)) {
                    return $type.$text.$type;
                } else {
                    return '<u>'.$text.'</u>';
                }
                    // no break
            case '^^':
                return '<sup>'.$text.'</sup>';
            case ',,':
                return '<sub>'.$text.'</sub>';
            case '<!--':
                return '<!--'.$text.'-->';
            case '{{|':
                return '<poem style="border: 2px solid #d6d2c5; background-color: #f9f4e6; padding: 1em;">'.$text.'</poem>';
            case '<nowiki>':
                return '<nowiki>'.$text.'</nowiki>';
            case '{{{':
                if (self::startsWith($text, '#!html')) {
                    $html = substr($text, 6);
                    $html = htmlspecialchars_decode($html);
                    return '<html>'.$html.'</html>';
                } elseif (self::startsWithi($text, '#!syntax') && preg_match('/#!syntax ([^\s]*)/', $text, $match)) {
                    return '<syntaxhighlight lang="'.$match[1].'" line="1">'.preg_replace('/#!syntax ([^\s]*)/', '', $text).'</syntaxhighlight>';
                } elseif (preg_match('/^#(?:([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})|([A-Za-z]+)) (.*)$/', $text, $color)) {
                    if (empty($color[1]) && empty($color[2])) {
                        return $text;
                    }
                    return '<span style="color: '.(empty($color[1])?$color[2]:'#'.$color[1]).'">'.$this->formatParser($color[3]).'</span>';
                } elseif (preg_match('/^\+([1-5]) (.*)$/', $text, $size)) {
                    for ($i=1; $i<=$size[1]; $i++) {
                        if (isset($big_before) && isset($big_after)) {
                            $big_before .= '<big>';
                            $big_after .= '</big>';
                        } else {
                            $big_before = '<big>';
                            $big_after = '</big>';
                        }
                    }

                    return $big_before.$this->formatParser($size[2]).$big_after;
                } elseif (preg_match('/^\-([1-5]) (.*)$/', $text, $size)) {
                    for ($i=1; $i<=$size[1]; $i++) {
                        if (isset($small_before) && isset($small_after)) {
                            $small_before .= '<small>';
                            $small_after .= '</small>';
                        } else {
                            $small_before = '<small>';
                            $small_after = '</small>';
                        }
                    }

                    return $small_before.$this->formatParser($size[2]).$small_after;
                } else {
                    return '<nowiki>' . $text . '</nowiki>';
                }
                // no break
            default:
                return $type.$text.$type;
            }
	}

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

	private function printFootnote() {
		if(count($this->fn)==0)
			return '';

		$result = $this->wapRender?'<hr>':'<hr><ol class="fn">';
		foreach($this->fn as $k => $fn) {
			$result .= $this->wapRender?'<p>':'<li>';
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
								.($this->wapRender?'</p>':'</li>');
		}
		$result .= $this->wapRender?'':'</ol>';
		$this->fn = array();
		return $result;
	}

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

	private function printToc(&$arr = null, $level = -1, $path = '') {
		if($level == -1) {
			$bak = $this->toc;
			$this->toc = array();
			$this->hParse($this->WikiPage->text);
			$result = ''
				.'<div id="toc">'
					.($this->wapRender!==false?'<h2>목차</h2>':'')
					.$this->printToc($this->toc, 0)
				.'</div>'
				.'';
			$this->toc = $bak;
			return $result;
		}

		if(empty($arr[0]))
			return '';

		$result  = '<div class="indent">';
		foreach($arr as $i => $item) {
			$readableId = $i+1;
			$result .= '<div><a href="#s-'.$path.$readableId.'">'.$path.$readableId.'</a>. '.$item['name'].'</div>'
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

	protected static function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	private static function seekEndOfLine($text, $offset=0) {
		return self::seekStr($text, "\n", $offset);
	}

	private static function seekStr($text, $str, $offset=0) {
		if($offset >= strlen($text) || $offset < 0)
			return strlen($text);
		return ($r=strpos($text, $str, $offset))===false?strlen($text):$r;
	}

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
