<?php

use Latte\Runtime as LR;

/** source: views/layouts/member/login.latte */
final class Template1181a815ef extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="wiki-content">
';
		if ($wiki['page']['data']['error']) /* line 2 */ {
			echo '    <div class="a e">
        <strong>';
			echo LR\Filters::escapeHtmlText($lang['msg']['error']) /* line 3 */;
			echo '</strong>
        <span>';
			echo LR\Filters::escapeHtmlText($lang['msg'][$wiki['page']['data']['error']]) /* line 4 */;
			echo '</span>
    </div>
';
		}
		echo '    <form data-pressdo-register id="loginform" name="loginform" method="POST" action="/member/login?redirect=';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escapeurl)($redirect))) /* line 6 */;
		echo '">
        <div data-pressdo-loginform class="c">
            <label data-pressdo-loginform for="username">Username</label>
            <input data-pressdo-formdata type="text" id="username" name="username" required>
        </div>
        <div data-pressdo-loginform class="c">
            <label data-pressdo-loginform for="password">Password</label>
            <input data-pressdo-formdata type="password" id="password" name="password" required>
        </div>
        <div data-pressdo-loginform class="b">
            <label data-pressdo-loginform>
                <input data-pressdo-formdata type="checkbox" name="autologin">
                <span data-pressdo-formdata>';
		echo LR\Filters::escapeHtmlText($lang['auth']['autologin']) /* line 18 */;
		echo '</span>
            </label>
        </div>
        <a data-pressdo-loginform class="a" href="/member/recover_password">[';
		echo LR\Filters::escapeHtmlText($lang['auth']['recover_password']) /* line 21 */;
		echo ']</a>
        <div class="btn-area">
            <a class="btn" href="/member/signup">';
		echo LR\Filters::escapeHtmlText($lang['auth']['signup']) /* line 23 */;
		echo '</a>
            <button type="submit" class="btn-blue btn" id="log-in">';
		echo LR\Filters::escapeHtmlText($lang['auth']['login']) /* line 24 */;
		echo '</button>
        </div>
    </form>
</div>';
		return get_defined_vars();
	}

}
