<?php

use Latte\Runtime as LR;

/** source: views/layouts/edit.latte */
final class Template531b930d0f extends Latte\Runtime\Template
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
		if ($wiki['alert']['alertbox']) /* line 6 */ {
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
		echo '    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
        <input type="hidden" name="token" value="';
		echo LR\Filters::escapeHtmlAttr($wiki['page']['data']['token']) /* line 11 */;
		echo '">
        <ul class="editor top">
            <li class="editor top">
                <button id="m" class="a e tr editor top" type="button" class="a">';
		echo LR\Filters::escapeHtmlText($lang['editor']['monaco']) /* line 14 */;
		echo '</button>
            </li>
            <li class="editor top">
                <button id="r" class="e editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['raw']) /* line 17 */;
		echo '</button>
            </li>
            <li class="editor top">
                <button id="p" class="e editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['preview']) /* line 20 */;
		echo '</button>
            </li>
            <li class="t editor top">
                <div class="editor top">
                    <button id="btn-bold" class="editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['bold']) /* line 24 */;
		echo '</button>
                </div><div class="editor top">
                    <button id="btn-italic" class="editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['italic']) /* line 26 */;
		echo '</button>
                </div><div class="editor top">
                    <button id="btn-strike" class="editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['strike']) /* line 28 */;
		echo '</button>
                </div><div class="editor top">
                    <button id="btn-link" class="editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['link']) /* line 30 */;
		echo '</button>
                </div><div class="editor top">
                    <button id="btn-file" class="editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['file']) /* line 32 */;
		echo '</button>
                </div><div class="editor top">
                    <button id="btn-ref" class="editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['ref']) /* line 34 */;
		echo '</button>
                </div><div class="editor top">
                    <button id="btn-template"n class="editor top" type="button">';
		echo LR\Filters::escapeHtmlText($lang['editor']['template']) /* line 36 */;
		echo '</button>
                </div>
            </li>
        </ul>
        <div class="c editor">
            <div id="m" class="a editor">
                <div id="monaco" class="editor showUnused"></div>
            </div><div id="r" class="editor">
                <textarea class="editor" id="pressdo-anchor" name="content">';
		echo LR\Filters::escapeHtmlText($wiki['page']['data']['editor']['raw']) /* line 44 */;
		echo '</textarea>
            </div><div id="p" class="p editor"></div>
        </div>
        <div class="g comment">
            <label class="comment" for="logInput">';
		echo LR\Filters::escapeHtmlText($lang['editor']['summary']) /* line 48 */;
		echo '</label>
            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
        </div>
        <label><input type="checkbox" name="agree" id="agree"> <span>';
		echo stripslashes($config['edit_agree_text']) /* line 51 */;
		echo '</span></label>
';
		if (!$wiki['session']['member']['username']) /* line 52 */ {
			echo '            <p data-pressdo-warning-unlogined>';
			echo LR\Filters::escapeHtmlText(str_replace('@1@', $wiki['session']['ip'], $lang['msg']['unlogined_edit'])) /* line 53 */;
			echo '</p>
';
		}
		echo '        <div class="btn-area">
            <button class="btn-blue btn btn-wideright" type="button" id="ef" editor-msg="';
		echo LR\Filters::escapeHtmlAttr($lang['msg']['please_agree']) /* line 56 */;
		echo '">저장</button>
        </div>
    </form>
</div>';
		return get_defined_vars();
	}

}
