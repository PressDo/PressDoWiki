<?php

use Latte\Runtime as LR;

/** source: views/layouts/RecentChanges.latte */
final class Template91ced1b7cd extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="wiki-content">
    <div>
        <ol data-pressdo-recentmenu>
            <li data-pressdo-recentmenu><a href="/RecentChanges?logtype=all">[전체]</a></li>
            <li data-pressdo-recentmenu><a href="/RecentChanges?logtype=create">[새 문서]</a></li>
            <li data-pressdo-recentmenu><a href="/RecentChanges?logtype=delete">[삭제]</a></li>
            <li data-pressdo-recentmenu><a href="/RecentChanges?logtype=move">[이동]</a></li>
            <li data-pressdo-recentmenu><a href="/RecentChanges?logtype=revert">[되돌림]</a></li>
        </ol>
        <table data-pressdo-recent-changes>
            <colgroup>
                <col>
                <col style="width: 25%;">
                <col style="width: 22%;">
            </colgroup>
            <thead>
                <tr>
                    <th data-pressdo-recent-changes>항목</th>
                    <th data-pressdo-recent-changes>수정자</th>
                    <th data-pressdo-recent-changes>수정 시간</th>
                </tr>
            </thead>
            <tbody>
';
		$iterations = 0;
		foreach ($wiki['page']['data']['content'] as $he) /* line 24 */ {
			if ($he['document']['namespace'] == $namespace['document'] && $config['force_show_namespace'] === false) /* line 25 */ {
				$ns = '' /* line 26 */;
			}
			else /* line 27 */ {
				$ns = $he['document']['namespace'].':' /* line 28 */;
			}
			echo "\n";
			$title = $ns.$he['document']['title'] /* line 31 */;
			$Diff = $he['count'] /* line 32 */;
			echo '                    
';
			if ($Diff > 0) /* line 34 */ {
				$color = 'green' /* line 35 */;
				$flag = '+' /* line 36 */;
			}
			elseif ($Diff == 0) /* line 37 */ {
				$color = 'gray' /* line 38 */;
				$flag = '' /* line 39 */;
			}
			elseif ($Diff < 0) /* line 40 */ {
				$color = 'red' /* line 41 */;
				$flag = '' /* line 42 */;
			}
			echo '
                    <tr data-pressdo-recent-changes class="n">
                        <td data-pressdo-recent-changes>
                            <a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($title))) /* line 47 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($title) /* line 47 */;
			echo '</a> 
                            <a href="/history/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($title))) /* line 48 */;
			echo '">[역사]</a>
';
			if ($he['logtype'] !== 'create') /* line 49 */ {
				echo '                            <a href="/diff/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($title))) /* line 49 */;
				echo '?rev=';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($he['rev'])) /* line 49 */;
				echo '">[비교]</a>
';
			}
			echo '                            <a href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($title))) /* line 50 */;
			echo '">[토론]</a>
                            <span>(<span data-pressdo-history-';
			echo LR\Filters::escapeHtmlAttrUnquoted($color) /* line 51 */;
			echo '>';
			echo LR\Filters::escapeHtmlText($flag.$Diff) /* line 51 */;
			echo '</span>)</span>
                        </td>
                        <td data-pressdo-recent-changes><div data-pressdo-popoever>
';
			if ($he['author'] === null) /* line 54 */ {
				echo '                            <a data-pressdo-recent-user style="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss($he['style'])) /* line 55 */;
				echo '" data-pressdo-user-ip>';
				echo LR\Filters::escapeHtmlText($he['ip']) /* line 55 */;
				echo '</a>
';
			}
			else /* line 56 */ {
				echo '                            <a data-pressdo-recent-user style="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss($he['style'])) /* line 57 */;
				echo '" data-pressdo-user-member>';
				echo LR\Filters::escapeHtmlText($he['author']) /* line 57 */;
				echo '</a>
';
			}
			echo '                        </div></td>
                        <td data-pressdo-recent-changes><time datetime="';
			echo LR\Filters::escapeHtmlAttr(date('Y-m-d\TH:i:s', $he['date']-32400)) /* line 60 */;
			echo '">';
			echo LR\Filters::escapeHtmlText(date('Y-m-d H:i:s', $he['date'])) /* line 60 */;
			echo '</time></td>
                    </tr>
';
			if (strlen($he['log']) > 0 || $he['logtype'] !== 'modify') /* line 62 */ {
				echo '                    <tr data-pressdo-recent-changes>
                        <td data-pressdo-recent-changes colspan="3">
                            <span data-pressdo-recent-changes>';
				echo LR\Filters::escapeHtmlText($he['log']) /* line 64 */;
				echo '</span>
                            <i>
';
				$ʟ_switch = ($he['logtype']) /* line 66 */;
				if (false) {
				}
				elseif (in_array($ʟ_switch, ['revert'], true)) /* line 67 */ {
					echo '                                    (';
					echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['log']['revert'], '@1@', $he['target_rev'])) /* line 68 */;
					echo ')
';
				}
				elseif (in_array($ʟ_switch, ['delete', 'create'], true)) /* line 69 */ {
					echo '                                    (';
					echo LR\Filters::escapeHtmlText($lang['log'][$he['logtype']]) /* line 70 */;
					echo ')
';
				}
				elseif (in_array($ʟ_switch, ['move'], true)) /* line 71 */ {
					echo '                                    (';
					echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['log']['move'], ['@1@' => $he['from'], '@2@' => $he['to']])) /* line 72 */;
					echo ')
';
				}
				echo '                            </i>
                        </td>
                    </tr>
';
			}
			$iterations++;
		}
		echo '            </tbody>
        </table>
    </div>
</div>';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['he' => '24'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
