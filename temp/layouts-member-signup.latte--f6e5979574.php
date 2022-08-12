<?php

use Latte\Runtime as LR;

/** source: views/layouts/member/signup.latte */
final class Templatef6e5979574 extends Latte\Runtime\Template
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
			echo '></span>
    </div>
';
		}
		if ($mode === 0 || empty($mode)) /* line 6 */ {
			echo '        ';
			echo LR\Filters::escapeHtmlText($_SESSION['MAIL_CHECK'] = true) /* line 8 */;
			echo '
        <form data-pressdo-register method="POST" id="regform" name="regform" action="/member/signup">
            <div data-pressdo-loginform class="c">
                <label data-pressdo-loginform for="email">';
			echo LR\Filters::escapeHtmlText($lang['auth']['email']) /* line 11 */;
			echo '</label>
                <input data-pressdo-formdata type="email" id="email" name="email">
                <p id="errmail" class="errmsg" v="none">';
			echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['msg']['err_required_input'] '@1@', $lang['auth']['email'])) /* line 13 */;
			echo '</p>
';
			if ($config['use_mail_whitelist'] === true) /* line 14 */ {
				echo '                    <p>';
				echo LR\Filters::escapeHtmlText($lang['msg:use_email_whitelist']) /* line 15 */;
				echo '</p>
                    <ul data-pressdo-mail-whitelist>
';
				$iterations = 0;
				foreach ($config['mail_whitelist'] as $wl) /* line 17 */ {
					echo '                        <li data-pressdo-mail-whitelist>';
					echo LR\Filters::escapeHtmlText($wl) /* line 17 */;
					echo '</li>
';
					$iterations++;
				}
				echo '                    </ul>
';
			}
			echo '            </div>
            <b>';
			echo LR\Filters::escapeHtmlText($lang['msg']['cannot_delete_account']) /* line 21 */;
			echo '</b>
            <button type="button" class="btn-blue btn" id="chkmail">';
			echo LR\Filters::escapeHtmlText($lang['auth']['signup_submit']) /* line 22 */;
			echo '</button>
            <div style="clear: both;">
                <div class="grecaptcha-badge" data-style="inline" style="width: 256px; height: 60px; box-shadow: gray 0px 0px 5px;">
                    <div class="grecaptcha-logo">
                        <iframe title="reCAPTCHA" src="https://www.google.com/recaptcha/api2/anchor?ar=1&k=6Le0YCgUAAAAAPuP955bk3npzh_ymfSd53DpI74j&co=aHR0cHM6Ly90aGVzZWVkLmlvOjQ0Mw..&hl=ko&v=6OAif-f8nYV0qSFmq-D6Qssr&size=invisible&badge=inline&cb=8wsokgsumi1k" width="256" height="60" role="presentation" name="a-p1qimm5s15r" frameborder="0" scrolling="no" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation allow-modals allow-popups-to-escape-sandbox">
                        </iframe>
                    </div>
                    <div class="grecaptcha-error">
                    </div>
                    <textarea id="g-recaptcha-response-4" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid rgb(193, 193, 193); margin: 10px 25px; padding: 0px; resize: none; display: none;">
                    </textarea>
                </div>
                <iframe style="display: none;">
                </iframe>
            </div>
        </form>
    
';
		}
		elseif ($mode === 1) /* line 39 */ {
			echo '        <p>';
			echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['msg']['sent_email'] '@1@', $_POST['email'])) /* line 41 */;
			echo '</p>
        <ul>
            <li>';
			echo LR\Filters::escapeHtmlText($lang['msg:check_spam']) /* line 43 */;
			echo '</li>
            <li>';
			echo LR\Filters::escapeHtmlText($lang['msg:email_expiry']) /* line 44 */;
			echo '</li>
        </ul>
';
		}
		elseif ($mode === 3) /* line 46 */ {
			echo '        <p>';
			echo ($this->filters->replace)($lang['msg']['welcome'] '@1@', $_POST['username']) /* line 49 */;
			echo '</p>
';
		}
		elseif ($mode === 2) /* line 50 */ {
			echo '        <form data-pressdo-register id="signupform" name="signupform" method="POST" action="/member/signup">
            <div data-pressdo-loginform class="c">
                <label data-pressdo-loginform for="username">';
			echo LR\Filters::escapeHtmlText($lang['auth']['username']) /* line 54 */;
			echo '</label>
                <input data-pressdo-formdata type="text" id="username" name="username">
                <p id="erruser1" class="errmsg" v="none">';
			echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['msg']['err_required_input'] '@1@', $lang['auth']['username'])) /* line 56 */;
			echo '</p><p id="erruser2" class="errmsg" v="none">';
			echo LR\Filters::escapeHtmlText($lang['msg']['err_username_exists']) /* line 56 */;
			echo '</p><p id="erruser3" class="errmsg" v="none">';
			echo LR\Filters::escapeHtmlText($lang['msg']['err_username_format']) /* line 56 */;
			echo '</p>
            </div>
            <div data-pressdo-loginform class="c">
                <label data-pressdo-loginform for="password">';
			echo LR\Filters::escapeHtmlText($lang['auth']['password']) /* line 59 */;
			echo '</label>
                <input data-pressdo-formdata type="password" id="password" name="password">
                <p id="errpwd" class="errmsg" v="none">';
			echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['msg']['err_required_input'] '@1@', $lang['auth']['password'])) /* line 61 */;
			echo '</p>
            </div>
            <div data-pressdo-loginform class="c">
                <label data-pressdo-loginform for="password2">';
			echo LR\Filters::escapeHtmlText($lang['auth']['password2']) /* line 64 */;
			echo '</label>
                <input data-pressdo-formdata type="password" id="password2" name="password2">
                <p id="errpwd21" class="errmsg" v="none">';
			echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['msg']['err_required_input'] '@1@', $lang['auth']['password2'])) /* line 66 */;
			echo '</p><p id="errpwd22" class="errmsg" v="none">';
			echo LR\Filters::escapeHtmlText($lang['msg']['err_wrong_password2']) /* line 66 */;
			echo '</p>
            </div>
            <b>';
			echo LR\Filters::escapeHtmlText($lang['msg']['cannot_delete_account']) /* line 68 */;
			echo '</b>
            <button type="button" class="btn-blue btn" id="signup">';
			echo LR\Filters::escapeHtmlText($lang['auth']['signup_submit']) /* line 69 */;
			echo '</button>
        </form>
    
';
		}
		echo '</div>';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['wl' => '17'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
