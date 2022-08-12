<?php

use Latte\Runtime as LR;

/** source: views/layouts/history.latte */
final class Template436946463d extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="wiki-content">
    <form action="/diff/';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['page']['full_title'])) /* line 2 */;
		echo '" method="GET">
';
		if (!$wiki['page']['data']['prev_ver']) /* line 3 */ {
			$_p = 'data-pressdo-history-null' /* line 4 */;
		}
		else /* line 5 */ {
			$_p = 'href="/history/'.$wiki['page']['full_title'].'?until='.$wiki['page']['data']['prev_ver'].'"' /* line 6 */;
		}
		echo '        
';
		if (!$wiki['page']['data']['next_ver']) /* line 9 */ {
			$_f = 'data-pressdo-history-null' /* line 10 */;
		}
		else /* line 11 */ {
			$_f = 'href="/history/'.$wiki['page']['full_title'].'?from='.$wiki['page']['data']['next_ver'].'"' /* line 12 */;
		}
		echo '        <p><button class="btn bt-white" type="submit">';
		echo LR\Filters::escapeHtmlText($lang['history']['compare_revision']) /* line 14 */;
		echo '</button></p>
        <div class="bt-g">
            <a ';
		echo $_p /* line 16 */;
		echo ' data-pressdo-history-mv>
                <span ionicon ion-arrow-back></span> Prev
            </a>
            <a ';
		echo $_f /* line 19 */;
		echo ' data-pressdo-history-mv>
                Next <span ionicon ion-arrow-forward></span>
            </a>
        </div>
        <ul data-pressdo-history>
';
		$iterations = 0;
		foreach ($wiki['page']['data']['history'] as $d) /* line 24 */ {
			echo "\n";
			if ($d['count'] > 0) /* line 26 */ {
				$color = 'green' /* line 27 */;
				$flag = '+' /* line 28 */;
			}
			elseif ($d['count'] == 0) /* line 29 */ {
				$color = 'gray' /* line 30 */;
				$flag = '' /* line 31 */;
			}
			elseif ($d['count'] < 0) /* line 32 */ {
				$color = 'red' /* line 33 */;
				$flag = '' /* line 34 */;
			}
			echo '                <li data-pressdo-history><time datetime="';
			echo LR\Filters::escapeHtmlAttr(date('Y-m-d\TH:i:s',$d['date']-32400).'.000Z') /* line 36 */;
			echo '">';
			echo LR\Filters::escapeHtmlText(date('Y-m-d H:i:s',$d['date'])) /* line 36 */;
			echo '</time> 
                    <span data-pressdo-history-menu>(<a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($wiki['page']['full_title']))) /* line 37 */;
			echo '?rev=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($d['rev'])) /* line 37 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($lang['history']['view']) /* line 37 */;
			echo '</a> | 
                    <a href="/raw/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($wiki['page']['full_title']))) /* line 38 */;
			echo '?rev=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($d['rev'])) /* line 38 */;
			echo '">RAW</a> | 
                    <a href="/revert/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($wiki['page']['full_title']))) /* line 40 */;
			echo '?rev=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($d['rev'])) /* line 40 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($lang['history']['revert']) /* line 40 */;
			echo '</a> | 
                    <a href="/diff/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($wiki['page']['full_title']))) /* line 41 */;
			echo '?rev=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($d['rev'])) /* line 41 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($lang['history']['diff']) /* line 41 */;
			echo '</a>';
			if (!empty($d['ip']) && $d['ip'] == $wiki['session']['ip'] && !empty($wiki['session']['member']['username'])) /* line 41 */ {
				echo ' | 
                    <a href="/move_ip/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($wiki['page']['full_title']))) /* line 42 */;
				echo '?dt=';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($d['date'])) /* line 42 */;
				echo '">';
				echo LR\Filters::escapeHtmlText($lang['history']['move_ip']) /* line 42 */;
				echo '</a>';
			}
			echo '
                    )
                    </span>
                    <input data-pressdo-history-radio type="radio" name="oldrev" value="';
			echo LR\Filters::escapeHtmlAttr($d['rev']) /* line 48 */;
			echo '" v="inline-block"><input data-pressdo-history-radio type="radio" name="rev" value="';
			echo LR\Filters::escapeHtmlAttr($d['rev']) /* line 48 */;
			echo '"  v="inline-block">
                    <i>';
			$ʟ_switch = ($d['logtype']) /* line 49 */;
			if (false) {
				echo "\n";
			}
			elseif (in_array($ʟ_switch, ['revert'], true)) /* line 50 */ {
				echo '                            (';
				echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['log']['revert'], '@1@', $he['target_rev'])) /* line 51 */;
				echo ')
';
			}
			elseif (in_array($ʟ_switch, ['delete', 'create'], true)) /* line 52 */ {
				echo '                            (';
				echo LR\Filters::escapeHtmlText($lang['log'][$d['logtype']]) /* line 53 */;
				echo ')
';
			}
			elseif (in_array($ʟ_switch, ['move'], true)) /* line 54 */ {
				echo '                            (';
				echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['log']['move'], ['@1@' => $he['from'], '@2@' => $he['to']])) /* line 55 */;
				echo ')
                    ';
			}
			echo '</i>
                    <strong>r';
			echo LR\Filters::escapeHtmlText($d['rev']) /* line 57 */;
			echo '</strong> <span>(<span data-pressdo-history-';
			echo LR\Filters::escapeHtmlAttrUnquoted($color) /* line 57 */;
			echo '>';
			echo LR\Filters::escapeHtmlText($flag.$d['count']) /* line 57 */;
			echo '</span>)</span>
                    <div data-pressdo-history-menu style="display:inline;">
';
			if ($d['author'] === null) /* line 59 */ {
				echo '                            <span style="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss($d['style'])) /* line 59 */;
				echo '" data-pressdo-user-ip>';
				echo LR\Filters::escapeHtmlText($d['ip']) /* line 59 */;
				echo '</span>
';
			}
			if ($d['author'] !== null) /* line 60 */ {
				echo '                            <span style="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss($d['style'])) /* line 60 */;
				echo '" data-pressdo-user-member>';
				echo LR\Filters::escapeHtmlText($d['author']) /* line 60 */;
				echo '</span>
';
			}
			echo '                    </div>
                    (<span data-pressdo-history-gray>';
			echo LR\Filters::escapeHtmlText($d['log']) /* line 62 */;
			echo '</span>)
                </li>
';
			$iterations++;
		}
		echo '        </ul>
        <div class="bt-g">
            <a ';
		echo $_p /* line 67 */;
		echo ' data-pressdo-history-mv>
                <span ionicon ion-arrow-back></span> Prev
            </a>
            <a ';
		echo $_f /* line 70 */;
		echo ' data-pressdo-history-mv>
                Next <span ionicon ion-arrow-forward></span>
            </a>
        </div>
    </form>
</div>';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['d' => '24'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
