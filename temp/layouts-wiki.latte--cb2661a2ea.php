<?php

use Latte\Runtime as LR;

/** source: skin/layouts/wiki.latte */
final class Templatecb2661a2ea extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="wiki-content">
';
		if ($wiki->error->errbox) /* line 2 */ {
			echo '    <div class="a e">
        <strong>';
			echo LR\Filters::escapeHtmlText($wiki->lang->msg->error) /* line 3 */;
			echo '</strong>
        <span><';
			echo LR\Filters::escapeHtmlText($wiki->error->message) /* line 4 */;
			echo '</span>
    </div>
';
		}
		echo '    <div>
        <div id="categoryspace_top"></div>
        <div class="w">
            <div>
';
		if ($wiki->page->data->user !== false) /* line 10 */ {
			if ($wiki->page->data->user->admin === true) /* line 11 */ {
				echo '                <div onmouseover="this.style.borderTopColor=\'red\';" onmouseout="this.style.borderTopColor=\'orange\';" style="border-width: 5px 1px 1px; border-style: solid; border-color: orange gray gray; border-image: initial; padding: 10px; margin-bottom: 10px;">
                    <span>';
				echo LR\Filters::escapeHtmlText($wiki->lang->msg->this_is_admin) /* line 12 */;
				echo '</span>
                </div>
';
			}
			if ($wiki->page->data->user->block->blocked === true) /* line 14 */ {
				echo "\n";
				if ($wiki->page->data->user->block->until == 0) /* line 16 */ {
					$until = $wiki->lang->acl->forever /* line 17 */;
				}
				else /* line 18 */ {
					$until = ($this->filters->replace)($wiki->lang->until, '@1@', $wiki->page->data->user->block->until) /* line 19 */;
				}
				echo '
                    <div onmouseover="this.style.borderTopColor=\'blue\';" onmouseout="this.style.borderTopColor=\'red\';" style="border-width: 5px 1px 1px; border-style: solid; border-color: red gray gray; border-image: initial; padding: 10px; margin-bottom: 10px;">
                        <span>';
				echo LR\Filters::escapeHtmlText($wiki->lang->msg->this_is_blocked) /* line 23 */;
				echo ' (#';
				echo LR\Filters::escapeHtmlText($wiki->page->data->user->block->seq) /* line 23 */;
				echo ')</span>
                        <br><br>
                        ';
				echo LR\Filters::escapeHtmlText(($this->filters->replace)($wiki->lang->msg->this_is_blocked_user, ['@1@' => '<time datetime="'.$wiki->page->data->user->block->dt_html.'">'.$wiki->page->data->user->block->datetime.'</time>', '@2@' => $wiki->page->data->user->block->until])) /* line 25 */;
				echo '
                        <br>
                        ';
				echo LR\Filters::escapeHtmlText($wiki->lang->blocked_reason) /* line 27 */;
				echo ': ';
				echo LR\Filters::escapeHtmlText($wiki->page->data->user->block->memo) /* line 27 */;
				echo '
                    </div>
';
			}
		}
		echo '            </div>
            ';
		echo htmlspecialchars_decode($wiki->page->data->document->content) /* line 32 */;
		echo '
        </div>
    </div>
</div>';
		return get_defined_vars();
	}

}
