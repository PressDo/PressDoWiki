<?php

use Latte\Runtime as LR;

/** source: /home/pi/phs/pressdo/views/layouts/wiki.latte */
final class Template56b5a96555 extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="wiki-content">
';
		if ($error['errbox']) /* line 2 */ {
			echo '    <div class="a e">
        <strong>';
			echo LR\Filters::escapeHtmlText($lang['msg']['error']) /* line 3 */;
			echo '</strong>
        <span><';
			echo LR\Filters::escapeHtmlText($error['message']) /* line 4 */;
			echo '</span>
    </div>
';
		}
		if ($wiki->alert->alertbox) /* line 6 */ {
			echo '    <div class="a">
        <strong>';
			echo LR\Filters::escapeHtmlText($lang['msg']['alert']) /* line 7 */;
			echo '</strong>
        <span><';
			echo LR\Filters::escapeHtmlText($wiki['alert']['message']) /* line 8 */;
			echo '</span>
    </div>
';
		}
		echo '    <div>
        <div id="categoryspace_top"></div>
        <div class="w">
            <div>
';
		if ($wiki['page']['data']['user'] !== false) /* line 14 */ {
			if ($wiki['page']['data']['user']['admin'] === true) /* line 15 */ {
				echo '                <div onmouseover="this.style.borderTopColor=\'red\';" onmouseout="this.style.borderTopColor=\'orange\';" style="border-width: 5px 1px 1px; border-style: solid; border-color: orange gray gray; border-image: initial; padding: 10px; margin-bottom: 10px;">
                    <span>';
				echo LR\Filters::escapeHtmlText($lang['msg']['this_is_admin']) /* line 16 */;
				echo '</span>
                </div>
';
			}
			if ($wiki['page']['data']['user']['block']['blocked'] === true) /* line 18 */ {
				echo "\n";
				if ($wiki['page']['data']['user']['block']['until'] == 0) /* line 20 */ {
					$until = $lang['acl']['forever'] /* line 21 */;
				}
				else /* line 22 */ {
					$until = ($this->filters->replace)($lang['until'], '@1@', $wiki['page']['data']['user']['block']['until']) /* line 23 */;
				}
				echo '
                    <div onmouseover="this.style.borderTopColor=\'blue\';" onmouseout="this.style.borderTopColor=\'red\';" style="border-width: 5px 1px 1px; border-style: solid; border-color: red gray gray; border-image: initial; padding: 10px; margin-bottom: 10px;">
                        <span>';
				echo LR\Filters::escapeHtmlText($lang['msg']['this_is_blocked']) /* line 27 */;
				echo ' (#';
				echo LR\Filters::escapeHtmlText($wiki['page']['data']['user']['block']['seq']) /* line 27 */;
				echo ')</span>
                        <br><br>
                        ';
				echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['msg']['this_is_blocked_user'], ['@1@' => '<time datetime="'.$wiki['page']['data']['user']['block']['dt_html'].'">'.$wiki['page']['data']['user']['block']['datetime'].'</time>', '@2@' => $wiki['page']['data']['user']['block']['until']])) /* line 29 */;
				echo '
                        <br>
                        ';
				echo LR\Filters::escapeHtmlText($lang['blocked_reason']) /* line 31 */;
				echo ': ';
				echo LR\Filters::escapeHtmlText($wiki['page']['data']['user']['block']['memo']) /* line 31 */;
				echo '
                    </div>
';
			}
		}
		echo '            </div>
            ';
		echo htmlspecialchars_decode($wiki['page']['data']['document']['content']) /* line 36 */;
		echo '
        </div>
    </div>
</div>';
		return get_defined_vars();
	}

}
