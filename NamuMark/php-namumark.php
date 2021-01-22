<?php
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

class NamuMark_ {

    function __construct($wtext, $title) {

        $this->list_tag = array(
            array('*', 'ul'),
            array('1.', 'ol'),
            array('A.', 'ol style="list-style-type:upper-alpha"'),
            array('a.', 'ol style="list-style-type:lower-alpha"'),
            array('I.', 'ol style="list-style-type:upper-roman"'),
            array('i.', 'ol style="list-style-type:lower-roman"')
        );

        $this->multi_bracket = array(
            array(
                'open'	=> '{{{',
                'close' => '}}}',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
            array(
                'open'	=> '<pre>',
                'close' => '</pre>',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
            array(
                'open'	=> '{{|',
                'close' => '|}}',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
            array(
                'open'	=> '<nowiki>',
                'close' => '</nowiki>',
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
                'open'	=> '[[',
                'close' => ']]',
                'multiline' => false,
                'processor' => array($this,'linkProcessor')),
            array(
                'open'	=> '{{|',
                'close' => '|}}',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
            array(
                'open'	=> '{{',
                'close' => '}}',
                'multiline' => false,
                'processor' => array($this,'mediawikiProcessor')),
            array(
                'open'	=> '[',
                'close' => ']',
                'multiline' => false,
                'processor' => array($this,'macroProcessor')),
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
                'processor' => array($this,'textProcessor')),
        );

        $this->WikiPage = $wtext;
        //$this->title = $title;

        $this->refnames = array();

        $this->toc = array();
        $this->fn = array();
        $this->fn_cnt = 0;
        $this->prefix = '';
    }

    public function toHtml() {
        $this->whtml = $this->WikiPage;
        $this->whtml = $this->htmlScan($this->whtml);
        return $this->whtml;
    }

    protected function htmlScan($text) {
        $result = '';
        $len = strlen($text);
        $line = '';

        for($i=0;$i<$len;self::nextChar($text,$i)) {
            $now = self::getChar($text,$i);
            if($line == '' && $now == ' ' && $list = $this->listParser($text, $i)) {
                $result .= ''
                    .$list
                    .'';
                $line = '';
                $now = '';
                continue;
            }


            if(self::startsWith($text, '|', $i) && $table = $this->tableParser($text, $i)) {
                $result .= ''
                    .$table
                    .'';
                $line = '';
                $now = '';
                continue;
            }

            if($line == '' && self::startsWith($text, '>', $i) && $blockquote = $this->bqParser($text, $i)) {
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
                        .$this->lineParser($line, '')
                        .$innerstr
                        .'';
                    $line = '';
                    $now = '';
                    break;
                }
            }

            if($now == "\n") { // line parse
                $result .= $this->lineParser($line, '');
                $line = '';
            }
            else
                $line.=$now;
        }
        if($line != '')
            $result .= $this->lineParser($line, 'notn');
        return $result;
    }

    protected function bqParser($text, &$offset) {
        $len = strlen($text);
        $innerhtml = '';
        for($i=$offset;$i<$len;$i=self::seekEndOfLine($text, $i)+1) {
            $eol = self::seekEndOfLine($text, $i);
            if(!self::startsWith($text, '>', $i)) {
                // table end
                break;
            }
            $i+=1;
            $innerhtml .= '<p>' . $this->formatParser(substr($text, $i, $eol-$i)). "</p>";
        }
        if(empty($innerhtml))
            return false;

        $offset = $i-1;

        if(preg_match_all('/<p>(>*)?(.*?)<\/p>/', $innerhtml, $matches, PREG_SET_ORDER)) {
            $innerhtml = '';
            foreach($matches as $line => $match) {
                $match[2] = trim($match[2]);
                if(strlen($match[1]) == 0) {
                    $innerhtml .= $match[2] . "\n";
                } else {
                    if (isset($matches[$line - 1])) {
                        if (strlen($match[1]) > strlen($matches[$line - 1][1])) {
                            for ($n = 1; $n <= strlen($match[1]) - strlen($matches[$line - 1][1]); $n++) {
                                $innerhtml .= '<blockquote>' . "\n";
                            }
                            $innerhtml .= $match[2] . "\n";
                            if (isset($matches[$line + 1]) && strlen($match[1]) > strlen($matches[$line + 1][1])) {
                                for ($n = 1; $n <= strlen($match[1]) - strlen($matches[$line + 1][1]); $n++) {
                                    $innerhtml .= '</blockquote>' . "\n";
                                }
                            }
                        } elseif (strlen($match[1]) < strlen($matches[$line - 1][1])) {
                            for ($n = 1; $n <= strlen($matches[$line - 1][1]) - strlen($match[1]); $n++) {
                                $innerhtml .= '</blockquote>' . "\n";
                            }
                            $innerhtml .= $match[2] . "\n";
                        } elseif (strlen($match[1]) == strlen($matches[$line - 1][1])) {
                            $innerhtml .= '</br>' . $match[2] . "\n";
                        }
                    } elseif (!isset($matches[$line - 1])) {
                        for ($n = 1; $n <= strlen($match[1]); $n++) {
                            $innerhtml .= '<blockquote>' . "\n";
                        }
                        $innerhtml .= $match[2] . "\n";
                        if (isset($matches[$line + 1]) && strlen($match[1]) > strlen($matches[$line + 1][1])) {
                            for ($n = 1; $n <= strlen($match[1]) - strlen($matches[$line + 1][1]); $n++) {
                                $innerhtml .= '</blockquote>' . "\n";
                            }
                        }
                    }
                    if (!isset($matches[$line + 1])) {
                        for ($n = 1; $n <= strlen($match[1]); $n++) {
                            $innerhtml .= '</blockquote>' . "\n";
                        }
                    }
                }
            }

        }
        
        return '<blockquote>'.$innerhtml.'</blockquote>'."\n";
    }

    protected function linkProcessor($text, $type) {
        if(self::startsWithi($text, 'http://') || self::startsWithi($text, 'https://') || self::startsWithi($text, 'ftp://') || self::startsWithi($text, 'ftps://')) {
            $ex_link = explode('|', $text);
            if(count($ex_link) - 1 != 0 && isset($ex_link[count($ex_link) - 1]))
                return '['.$ex_link[0].' '.$ex_link[count($ex_link) - 1].']';
            else
                return '['.$ex_link[0].']';
        }
        $text = preg_replace('/(https?.*?(\.jpeg|\.jpg|\.png|\.gif))/', '<img src="$1">', $text);
        if(preg_match('/(.*)\|(\[\[파일:.*)\]\]/', $text, $filelink))
            return $filelink[2].'|link='.str_replace(' ', '_',$filelink[1]).']]';
        if(preg_match('/^(파일:.*?(?!\.jpeg|\.jpg|\.png|\.gif))\|(.*)/i', $text, $namu_image)) {
            $properties = explode("&", $namu_image[2]);

            foreach($properties as $n => $each_property) {
                if(preg_match('/^width=(.*)/i', $each_property, $width)) {
                    if(self::endsWith($width[1], '%'))
                        continue;
                    $imgwidth[1] = str_ireplace('px', '', $width[1]);
                    unset($properties[$n]);
                    continue;
                }

                if(preg_match('/^height=(.*)/i', $each_property, $height)) {
                    if(self::endsWith($height[1], '%'))
                        continue;
                    $imgheight[1] = str_ireplace('px', '', $height[1]);
                    unset($properties[$n]);
                    continue;
                }

                $properties[$n] = str_ireplace('align=', '', $each_property);
            }


            $property = '|';
            foreach($properties as $n => $each_property)
                $property .= $each_property.'|';

            if(isset($imgwidth) && isset($imgheight))
                $property .= $imgwidth[1] . 'x' . $imgheight[1] . 'px|';
            elseif(isset($imgwidth))
                $property .= $imgwidth[1].'px|';
            elseif(isset($imgheight))
                $property .= 'x'.$imgheight[1].'px|';

            $property = substr($property, 0, -1);

            return '[['.$namu_image[1].$property.']]';
        }
        return '[[' . $this->formatParser($text) . ']]';
    }

    protected function macroProcessor($text, $type) {
        $text = $this->formatParser($text);
        switch(strtolower($text)) {
            case 'br':
                return '<br>';
            case 'date':
            case 'datetime':
                return date('Y-m-d H:i:s');
            case '목차':
            case 'tableofcontents':
                return '__TOC__';
            case '각주':
            case 'footnote':
                return '<references />';
            default:
                if(self::startsWithi($text, 'include') && preg_match('/^include\((.+)\)$/i', $text, $include)) {
                    $include[1] = str_replace(',', '|', $include[1]);
                    $include[1] = urldecode($include[1]);
                    return '{{'.$include[1].'}}'."\n";
                }
                if(self::startsWithi($text, 'anchor') && preg_match('/^anchor\((.+)\)$/i', $text, $anchor))
                    return '<div id="'.$anchor[1].'"></div>';
                if(self::startsWith($text, '*') && preg_match('/^\*([^ ]*)([ ].+)?$/', $text, $note)) {
                    if(isset($note[1]) && isset($note[2]) && $note[1] !== '') {
                        foreach($this->refnames as $refname) {
                            if($refname === $note[1])
                                return '<ref name="'.$note[1].'" />';
                        }
                        array_push($this->refnames, $note[1]);
                        return '<ref name="' . $note[1] . '">' . $note[2] . '</ref>';
                    } elseif(isset($note[2]))
                        return '<ref>'.$note[2].'</ref>';
                    elseif(isset($note[1]))
                        return '<ref name="'.$note[1].'" />';
                }
                if(preg_match('/^(youtube|nicovideo)\((.*)\)$/i', $text, $video_code))
                    return $this->videoProcessor($video_code[2], strtolower($video_code[1]));

        }
        return '['.$text.']';
    }

    protected function videoProcessor($text, $service) {
        $text = str_replace('|', ',', $text);
        $options = explode(",", $text);
        $text = '';

        foreach($options as $key => $value) {
            if($key == 0) {
                $service = str_replace('nicovideo', 'nico', $service);
                $text .= '{{#evt:service='.$service.'|id='.$value;
                continue;
            }

            $option = explode("=", $value);
            if($option[0] == 'width') {
                $width = $option[1];
                continue;
            } elseif ($option[0] == 'height') {
                $height = $option[1];
                continue;
            } elseif (preg_match('/(\d+)x(\d+)/', $value, $match)) {
                $width = $match[1];
                $height = $match[2];
                continue;
            }

            $text .= '|'.$value;
        }

        if(isset($width) && isset($height))
            $text .= '|dimensions='.$width.'x'.$height;
        elseif(isset($width))
            $text .= '|dimensions='.$width;
        elseif(isset($height))
            $text .= '|dimensions=x'.$height;

        return $text.'}}';

    }

    protected function mediawikiProcessor($text, $type) {
        if($type == '{{')
            return '{{'.$text.'}}';
    }


    protected function lineParser($line, $type) {
		if($line == '----')
			return '<hr>';

		$line = $this->blockParser($line);

		if($type == 'notn')
			return $line;
		else
            return $line."\n";
	}

	protected function formatParser($line) {
		$line_len = strlen($line);
		for($j=0;$j<$line_len;self::nextChar($line,$j)) {
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
		return $line;
	}

	protected function bracketParser($text, &$now, $bracket) {
		$len = strlen($text);
		$cnt = 0;
		$done = false;

		$openlen = strlen($bracket['open']);
		$closelen = strlen($bracket['close']);

		for($i=$now;$i<$len;self::nextChar($text,$i)) {
			if(self::startsWith($text, $bracket['open'], $i) && !($bracket['open']==$bracket['close'] && $cnt>0)) {
				$cnt++;
				$done = true;
				$i+=$openlen-1; // �ݺ��� �� ������ ���̹Ƿ�
			}elseif(self::startsWith($text, $bracket['close'], $i)) {
				$cnt--;
				$i+=$closelen-1;
			}elseif(!$bracket['multiline'] && $text[$i] == "\n")
				return false;

			if($cnt == 0 && $done) {
				$innerstr = substr($text, $now+$openlen, $i-$now-($openlen+$closelen)+1);

				if((!strlen($innerstr)) ||($bracket['multiline'] && strpos($innerstr, "\n")===false))
					return false;
				$result = call_user_func_array($bracket['processor'],array($innerstr, $bracket['open']));
				$now = $i;
				return $result;
			}
		}
		return false;
	}
	
	protected static function getChar($string, $pointer){
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

	protected static function nextChar($string, &$pointer){
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

	protected static function startsWith($haystack, $needle, $offset = 0) {
		$len = strlen($needle);
		if(($offset+$len)>strlen($haystack))
			return false;
		return $needle == substr($haystack, $offset, $len);
	}

	protected static function startsWithi($haystack, $needle, $offset = 0) {
		$len = strlen($needle);
		if(($offset+$len)>strlen($haystack))
			return false;
		return strtolower($needle) == strtolower(substr($haystack, $offset, $len));
	}

	protected static function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	
	protected static function seekEndOfLine($text, $offset=0) {
		return ($r=strpos($text, "\n", $offset))===false?strlen($text):$r;
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

    protected function textProcessor($otext, $type) {
        if($type != '{{{' && $type != '<nowiki>')
            $text = $this->formatParser($otext);
        else
            $text = $otext;
        switch($type) {
            case '--':
            case '~~':
                if(!self::startsWith($text, 'item-') && !self::endsWith($text, 'UNIQ') && !self::startsWith($text, 'QINU') && !preg_match('/^.*?-.*-QINU/', $text) && !self::startsWith($text, 'h-'))
                    return '<s>'.$text.'</s>';
                else
                    return $type.$text.$type;
            case '__':
                if(preg_match('/TOC/', $text) || preg_match('/^.*?(\.jpeg|\.jpg|\.png|\.gif)/', $text))
                    return $type.$text.$type;
                else
                    return '<u>'.$text.'</u>';
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
                if(self::startsWith($text, '#!html')) {
                    $html = substr($text, 6);
                    $html = htmlspecialchars_decode($html);
                    return '<html>'.$html.'</html>';
                } elseif(self::startsWithi($text, '#!syntax') && preg_match('/#!syntax ([^\s]*)/', $text, $match)) {
                    return '<syntaxhighlight lang="'.$match[1].'" line="1">'.preg_replace('/#!syntax ([^\s]*)/', '', $text).'</syntaxhighlight>';
                } elseif(preg_match('/^#(?:([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})|([A-Za-z]+)) (.*)$/', $text, $color)) {
                    if(empty($color[1]) && empty($color[2]))
                        return $text;
                    return '<span style="color: '.(empty($color[1])?$color[2]:'#'.$color[1]).'">'.$this->formatParser($color[3]).'</span>';
                } elseif(preg_match('/^\+([1-5]) (.*)$/', $text, $size)) {
                    for ($i=1; $i<=$size[1]; $i++){
                        if(isset($big_before) && isset($big_after)) {
                            $big_before .= '<big>';
                            $big_after .= '</big>';
                        } else {
                            $big_before = '<big>';
                            $big_after = '</big>';
                        }
                    }

                    return $big_before.$this->formatParser($size[2]).$big_after;
                } elseif(preg_match('/^\-([1-5]) (.*)$/', $text, $size)) {
                    for ($i=1; $i<=$size[1]; $i++){
                        if(isset($small_before) && isset($small_after)) {
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
            default:
                return $type.$text.$type;
        }
    }

    protected function listParser($text, &$offset) {
        $listTable = array();
        $len = strlen($text);
        $lineStart = $offset;

        $quit = false;
        for($i=$offset;$i<$len;$before=self::nextChar($text,$i)) {
            $now = self::getChar($text,$i);
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
        $result = ($tag=='indent'?'':'<'.$tag.($start!=1?' start="'.$start.'"':'').'>');
        foreach($arr as $li) {
            $text = $this->blockParser($li['text']).$this->listDraw($li['childNodes']);
            $result .= $tag=='indent'?$text:'<li>'.$text.'</li>';
        }
        $result .= ($tag=='indent'?'':'</'.$tag.'>');
        $result .= "\n";
        return $result;
    }

    protected function blockParser($block) {
        return $this->formatParser($block);
    }

    protected function renderProcessor($text, $type) {

    }

}

