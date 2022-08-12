<?php

use Latte\Runtime as LR;

/** source: /home/pi/phs/pressdo/skins/buma/layout.latte */
final class Templateba0fa536b1 extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="top-anchor"></div>
    <nav class="nav navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a href="/" class="navbar-item">';
		if ($wiki->config->logo_url) /* line 4 */ {
			echo '<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->config->logo_url)) /* line 4 */;
			echo '">';
		}
		echo LR\Filters::escapeHtmlText($wiki->config->site_name) /* line 4 */;
		echo '</a>
            <a class="navbar-burger" :class="{ \'is-active\': isNavbarActive }" @click.prevent="toggleNavbarBurger">
                <span></span>
                <span></span>
                <span></span>
            </a>
        </div>
        <div class="navbar-menu" :class="{ \'is-active\': isNavbarActive }" id="mainNavbar">
            <div class="navbar-start">
                <a href="/RecentChanges" class="navbar-item">
                    <span class="icon">
                        <i class="fas fa-binoculars"></i> </span
                    >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->RecentChanges) /* line 16 */;
		echo '
                </a>
                <a href="/RecentDiscuss" class="navbar-item">
                    <span class="icon">
                        <i class="far fa-comments"></i> </span
                    >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->RecentDiscuss) /* line 21 */;
		echo '
                </a>
                <div class="navbar-item has-dropdown is-hoverable">
                    <a href="#" class="navbar-link" @click.prevent="toggleNavbar">
                        <span class="icon" v-if="icon">
                            <i :class="icon"></i>
                        </span>&nbsp; {}
                    </a>
                    <div class="navbar-dropdown" :class="{\'is-right\': rightDropdown}" :style="dropdownStyle">
                        <slot></slot>
                    </div>
                </div>
                <div icon="fas fa-cogs" label="';
		echo LR\Filters::escapeHtmlAttr($wiki->lang->menu->tools) /* line 33 */;
		echo '">
                <a class="navbar-item" href="/NeededPages">
                    <span class="icon">
                        <i class="fas fa-beer"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->NeededPages) /* line 37 */;
		echo '
                </a>
                <a class="navbar-item" href="/OrphanedPages">
                    <span class="icon">
                        <i class="far fa-frown"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->OrphanedPages) /* line 42 */;
		echo '
                </a>
                <a class="navbar-item" href="/UncategorizedPages">
                    <span class="icon">
                        <i class="far fa-question-circle"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->UncategorizedPages) /* line 47 */;
		echo '
                </a>
                <a class="navbar-item" href="/OldPages">
                    <span class="icon">
                        <i class="fas fa-pause"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->OldPages) /* line 52 */;
		echo '
                </a>
                <a class="navbar-item" href="/ShortestPages">
                    <span class="icon">
                        <i class="far fa-thumbs-down"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->ShortestPages) /* line 57 */;
		echo '
                </a>
                <a class="navbar-item" href="/LongestPages">
                    <span class="icon">
                        <i class="far fa-thumbs-up"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->LongestPages) /* line 62 */;
		echo '
                </a>
                <a class="navbar-item" href="/BlockHistory">
                    <span class="icon">
                        <i class="fas fa-ban"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->BlockHistory) /* line 67 */;
		echo '
                </a>
                <a class="navbar-item" href="/RandomPage">
                    <span class="icon">
                        <i class="fas fa-random"></i> </span
                        >&nbsp; RandomPage
                </a>
                <a class="navbar-item" to="/Upload">
                    <span class="icon">
                        <i class="fas fa-cloud-upload-alt"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->Upload) /* line 77 */;
		echo '
                </a>
                <a class="navbar-item" to="/License">
                    <span class="icon">
                        <i class="far fa-copyright"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->License) /* line 82 */;
		echo '
                </a>
';
		$sessionArray = json_decode(json_encode($wiki->session), true) /* line 84 */;
		if (count($sessionArray['menus']) > 0) /* line 85 */ {
			$iterations = 0;
			foreach ($sessionArray['menus'] as $m) /* line 86 */ {
				echo '                    <a href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($m['l'])) /* line 86 */;
				echo '" class="navbar-item">';
				echo LR\Filters::escapeHtmlText($wiki->lang->page->{
					$m['t'] }) /* line 86 */;
				echo '</a>
';
				$iterations++;
			}
		}
		echo '                </b-dropdown>
            </div>
            <div class="navbar-end">
                <div class="navbar-item is-hidden-touch">
                    <search-form></search-form>
                </div>
';
		if ($wiki->session->member) /* line 94 */ {
			echo '                    <b-dropdown right-dropdown icon="fas fa-user" :label="$store.state.session.member.username">
                        <a href="#" @click.prevent="$modal.show(\'theseed-setting\')" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-wrench"></i>
                            </span>&nbsp;
                            스킨 설정
                        </a>
                        <a to="/member/mypage" class="navbar-item">
                            <span class="icon">
                                <i class="far fa-user-circle"></i>
                            </span>&nbsp;
                            내정보
                        </a>
                        <a href="doc_action_link(user_doc($store.state.session.member.username), \'w\')" class="navbar-item">
                            <span class="icon">
                                <i class="far fa-sticky-note"></i>
                            </span>&nbsp;
                            내 사용자 문서
                        </a>
                        <div class="navbar-divider"></div>
                        <a href="contribution_author_link($store.state.session.member.username)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-line"></i>
                            </span>&nbsp;
                            내 문서 기여 목록
                        </a>
                        <a href="contribution_author_link_discuss($store.state.session.member.username)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-bar"></i>
                            </span>&nbsp;
                            내 토론 기여 목록
                        </a>
                        <a to="/member/starred_documents" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-bookmark"></i>
                            </span>&nbsp;
                            별찜한 문서들
                        </a>
                        <div class="navbar-divider"></div>
                        <a href="{ path: \'/member/logout\', query: { redirect: $route.fullPath } }" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </span>&nbsp;
                            로그아웃
                        </a>
                    </b-dropdown>
';
		}
		echo '
                <template v-else>
                    <b-dropdown right-dropdown icon="fas fa-user-secret" label="익명">
                        <a href="#" @click.prevent="$modal.show(\'theseed-setting\')" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-wrench"></i>
                            </span>&nbsp;
                            스킨 설정
                        </a>
                        <a href="contribution_ip_link($store.state.session.ip)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-line"></i>
                            </span>&nbsp;
                            내 문서 기여 목록
                        </a>
                        <a href="contribution_ip_link_discuss($store.state.session.ip)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-bar"></i>
                            </span>&nbsp;
                            내 토론 기여 목록
                        </a>
                        <div class="navbar-divider"></div>
                        <a href="{ path: \'/member/login\', query: { redirect: $route.fullPath } }" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </span>&nbsp;
                            로그인
                        </a>
                    </b-dropdown>
                </template>
            </div>
        </div>
    </nav>
    <nav class="nav navbar is-hidden-desktop mobile-search-navbar">
        <div class="navbar-brand">
            <div class="navbar-item">
                <search-form></search-form>
            </div>
        </div>

    </nav>
    <section id="wiki-main-title"';
		echo ($ʟ_tmp = array_filter([$wiki->error || $wiki->page->data->view_name == 'notfound' ? 'is-danger' : 'is-primary', 'hero'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 183 */;
		echo '>
        <div class="hero-body">
            <div class="container">
                <h1 class="title">';
		echo LR\Filters::escapeHtmlText($wiki->page->full_title) /* line 186 */;
		echo '</h1>
                <h2 class="subtitle">
';
		if ($wiki->page->view_name === 'wiki' && $wiki->page->data->date) /* line 188 */ {
			echo '                    <span>
                        ';
			echo ($this->filters->replace)($wiki->lang->document->lastchanged, '@1@', '<time datetime="'.date("Y-m-d\TH:i:s", $wiki->page->data->date - 32400).'.000Z'.'">'.date("Y-m-d H:i:s", $wiki->page->data->date).'</time>') /* line 190 */;
			echo '
                    </span>
';
		}
		elseif ($wiki->page->view_name === 'notfound') /* line 192 */ {
			echo '                    <span> 존재하지 않는 문서입니다. 직접 자유롭게 기여해보세요! </span>
';
		}
		else /* line 194 */ {
			echo '                    <span> Powered by PressDo </span>
';
		}
		echo '                </h2>
            </div>
        </div>
        <div class="hero-foot">
            <nav class="tabs is-left is-boxed" id="wiki-article-menu">
                <div class="container">
';
		if ($wiki->page->data->document) /* line 203 */ {
			echo '                    <ul>
                        <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name === 'wiki' ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 205 */;
			echo '>
                            <a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 206 */;
			echo '">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 읽기</span>
                            </a>
                        </li>
                        <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name === 'edit' ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 213 */;
			echo '>
                            <a href="/edit/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 214 */;
			echo '" class="edit-anchor">
                                <span class="icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 편집</span>
                            </a>
                        </li>
                        <li';
			echo ($ʟ_tmp = array_filter([in_array($wiki->page->view_name, ['thread', 'thread_list', 'thread_list_close']) ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 221 */;
			echo '>
                            <a href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 222 */;
			echo '">
                                <span class="icon">
                                    <i class="far fa-comments"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 토론</span>
                            </a>
                        </li>
                        <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name === 'move' ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 229 */;
			echo '>
                            <a href="/move/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 230 */;
			echo '">
                                <span class="icon">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 이동</span>
                            </a>
                        </li>
                        <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name === 'delete' ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 237 */;
			echo '>
                            <a href="/delete/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 238 */;
			echo '">
                                <span class="icon">
                                    <i class="far fa-trash-alt"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 삭제</span>
                            </a>
                        </li>
                        <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name === 'backlink' ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 245 */;
			echo '>
                            <a href="/backlink/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 246 */;
			echo '">
                                <span class="icon">
                                    <i class="fas fa-random"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 역링크</span>
                            </a>
                        </li>
                        <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name === 'history' ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 253 */;
			echo '>
                            <a href="/history/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 254 */;
			echo '">
                                <span class="icon">
                                    <i class="fas fa-history"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 역사</span>
                            </a>
                        </li>
                        <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name === 'acl' ? 'is-active' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 261 */;
			echo '>
                            <a href="/acl/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 262 */;
			echo '">
                                <span class="icon">
                                    <i class="fas fa-key"></i>
                                </span>
                                <span class="wiki-article-menu-text"> ACL</span>
                            </a>
                        </li>

                        <!-- [나무위키] main.b9faec2b37c8d51a1d7e.js 참고함 -->
';
			if ($wiki->page->view_name === 'wiki') /* line 271 */ {
				echo '                        <li';
				echo ($ʟ_tmp = array_filter([$wiki->page->data->starred ? 'starred' : null, 'star-tab'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 271 */;
				echo '>
                        ';
				if ($wiki->page->data->starred) /* line 272 */ {
					echo '
                            <a href="/member/unstar/';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 273 */;
					echo '">
                                <span class="icon">
                                    <i class="fas fa-star"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 별찜 해제 (</span><span class="star-count">';
					echo LR\Filters::escapeHtmlText((($ʟ_tmp = $wiki->page->data ?? null) === null ? null : $ʟ_tmp->star_count)) /* line 277 */;
					echo '</span><span class="wiki-article-menu-text">)</span>
                            </a>
';
				}
				else /* line 279 */ {
					echo '                            <a href="/member/star/';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 280 */;
					echo '">
                                <span class="icon">
                                    <i class="fas fa-star"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 별찜 (</span><span class="star-count">';
					echo LR\Filters::escapeHtmlText((($ʟ_tmp = $wiki->page->data ?? null) === null ? null : $ʟ_tmp->star_count)) /* line 284 */;
					echo '</span><span class="wiki-article-menu-text">)</span>
                            </a>
';
				}
				echo '                        </li>
';
			}
			echo '
                        <!-- [나무위키] main.a65ef46d6b3879416d5f.js 및 main.b9faec2b37c8d51a1d7e.js 참고함 -->
';
			if ($wiki->page->data->user) /* line 290 */ {
				echo '                        <li>
                            <a href="/contribution/author/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->data->document->title))) /* line 291 */;
				echo '/document">
                                <span class="icon">
                                    <i class="fas fas fa-chart-line"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 기여내역</span>
                            </a>
                        </li>
';
			}
			echo '                    </ul>
';
		}
		else /* line 299 */ {
			echo '                    <ul>
                        <li class="is-active">
                            <a href="#">
                                <span class="icon">
                                    <i class="fas fa-cogs"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 특수문서</span>
                            </a>
                        </li>
                    </ul>
';
		}
		echo '            </div>
                </nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
';
		if ($wiki->session->member && $wiki->session->member->user_document_discuss && $wiki->config->hide_user_document_discuss !== $wiki->session->member->user_document_discuss) /* line 317 */ {
			echo '            <b-notification
                class="is-link">
                <div class="notification is-link" v-if="destroyed">
                    <button class="delete" v-if="deleteButton" @click.prevent="deleteMyself"></button>
                    <slot></slot>
                </div>
                ';
			echo LR\Filters::escapeHtmlText(($this->filters->replace)($wiki->lang->msg->user_thread_exists, '@1@', '/discuss/'.rawurlencode($wiki->session->member->username))) /* line 324 */;
			echo '
            </b-notification>
';
		}
		echo '
            <b-notification v-if="$store.state.config[\'wiki.sitenotice\']">
                <span v-html="$store.state.config[\'wiki.sitenotice\']"></span>
            </b-notification>

            <div class="wiki-article content" @dblclick="doBehaviorWhenDblClick">
                ';
		echo $wiki->skin->inner_layout /* line 332 */;
		echo '

                <skin-license v-if="$store.state.page.viewName === \'license\'"></skin-license>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="content has-text-centered">
                <p>
                    <span v-html="$store.state.config[\'wiki.copyright_text\']"></span>
                    <span v-html="$store.state.config[\'wiki.footer_text\']"></span>

                    theseed-skin-buma by LiteHell, PressDo engine by PRASEOD-
                </p>
            </div>
        </div>
    </footer>
    <setting>
        <setting-item-select label="더블 클릭시 행동" ckey="buma.behaviorWhenDblClick" default="skinDefault" note="더블 클릭시의 행동입니다">
            <option value="edit">편집</option>
            <option value="history">역사</option>
            <option value="doNohting">아무것도 하지 않음</option>
            <option value="skinDefault">스킨 기본값</option>
        </setting-item-select>
        <setting-item-select
            label="점프 버튼 활성화"
            ckey="buma.enableJumpButtons"
            default="skinDefault"
            note="위/아래로 이동하는 점프 버튼을 표시할지의 여부입니다"
        >
            <option value="yes">활성화</option>
            <option value="no">비활성화</option>
            <option value="skinDefault">스킨 기본값</option>
        </setting-item-select>
    </setting>
    <jump-buttons></jump-buttons>
</div>
<style>
.mobile-search-navbar .navbar-brand, .mobile-search-navbar .navbar-brand .navbar-item, .mobile-search-navbar .navbar-brand .navbar-item > * {
    width: 100%;
}
</style>

<script>
import Common from \'~/mixins/common\';
import Setting from \'~/components/setting\';
import LocalDate from \'~/components/localDate\';
import SettingItemCheckbox from \'~/components/settingItemCheckbox\';
import SettingItemSelect from \'~/components/settingItemSelect\';
import SearchForm from \'./components/searchForm\';
import SkinLicense from \'./components/skinLicense\';
import BNotification from \'./components/b-notification\';
import JumpButtons from \'./components/jumpButtons\';
import BDropdown from \'./components/b-dropdown\';
if (process.browser) {
    try {
        require(\'./js/all.min.js\');
    } catch (e) {
        console.log(e.stack);
    }
}
export default {
    mixins: [Common],
    components: {
        Setting,
        LocalDate,
        SettingItemSelect,
        SettingItemCheckbox,
        SearchForm,
        SkinLicense,
        BNotification,
        JumpButtons,
        BDropdown
    },
    loadingBarColor(isDark) {
        return isDark ? \'white\' : \'black\';
    },
    data: function () {
        return {
            isNavbarActive: false
        };
    },
    methods: {
        doBehaviorWhenDblClick() {
            if (!this.$store.state.page.data.document) return;
            const action = this.$store.state.localConfig[\'buma.behaviorWhenDblClick\'];
            switch (action) {
                case \'edit\':
                case \'history\':
                    const link = this.doc_action_link(this.$store.state.page.data.document, action);
                    this.$router.push(link);
                    break;
                case \'doNothing\':
                case \'skinDefault\':
                default:
                    break;
            }
        },
        toggleNavbarBurger() {
            this.isNavbarActive = !this.isNavbarActive;
        }
    }
};
</script>';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['m' => '86'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
