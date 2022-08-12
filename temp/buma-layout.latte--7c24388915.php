<?php

use Latte\Runtime as LR;

/** source: skin/buma/layout.latte */
final class Template7c24388915 extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="top-anchor"></div>
    <nav class="nav navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a href="/" class="navbar-item"
                >';
		if ($wiki->config->logo_url) /* line 5 */ {
			echo '<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->config->logo_url)) /* line 5 */;
			echo '">';
		}
		echo LR\Filters::escapeHtmlText($wiki->config->site_name) /* line 5 */;
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
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->RecentChanges) /* line 17 */;
		echo '
                </a>
                <a href="/RecentDiscuss" class="navbar-item">
                    <span class="icon">
                        <i class="far fa-comments"></i> </span
                    >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->RecentDiscuss) /* line 22 */;
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
		echo LR\Filters::escapeHtmlAttr($wiki->lang->menu->tools) /* line 34 */;
		echo '">
                <a class="navbar-item" href="/NeededPages">
                    <span class="icon">
                        <i class="fas fa-beer"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->NeededPages) /* line 38 */;
		echo '
                </a>
                <a class="navbar-item" href="/OrphanedPages">
                    <span class="icon">
                        <i class="far fa-frown"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->OrphanedPages) /* line 43 */;
		echo '
                </a>
                <a class="navbar-item" href="/UncategorizedPages">
                    <span class="icon">
                        <i class="far fa-question-circle"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->UncategorizedPages) /* line 48 */;
		echo '
                </a>
                <a class="navbar-item" href="/OldPages">
                    <span class="icon">
                        <i class="fas fa-pause"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->OldPages) /* line 53 */;
		echo '
                </a>
                <a class="navbar-item" href="/ShortestPages">
                    <span class="icon">
                        <i class="far fa-thumbs-down"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->ShortestPages) /* line 58 */;
		echo '
                </a>
                <a class="navbar-item" href="/LongestPages">
                    <span class="icon">
                        <i class="far fa-thumbs-up"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->LongestPages) /* line 63 */;
		echo '
                </a>
                <a class="navbar-item" href="/BlockHistory">
                    <span class="icon">
                        <i class="fas fa-ban"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->BlockHistory) /* line 68 */;
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
		echo LR\Filters::escapeHtmlText($wiki->lang->page->Upload) /* line 78 */;
		echo '
                </a>
                <a class="navbar-item" to="/License">
                    <span class="icon">
                        <i class="far fa-copyright"></i> </span
                        >&nbsp; ';
		echo LR\Filters::escapeHtmlText($wiki->lang->page->License) /* line 83 */;
		echo '
                </a>
';
		$sessionArray = json_decode(json_encode($wiki->session), true) /* line 85 */;
		if (count($sessionArray['menus']) > 0) /* line 86 */ {
			$iterations = 0;
			foreach ($sessionArray['menus'] as $m) /* line 87 */ {
				echo '                    <a href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($m['l'])) /* line 87 */;
				echo '" class="navbar-item">';
				echo LR\Filters::escapeHtmlText($wiki->lang->page->{
					$m['t'] }) /* line 87 */;
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
		if ($wiki->session->member) /* line 95 */ {
			echo '                    <b-dropdown right-dropdown icon="fas fa-user" :label="$store.state.session.member.username">
                        <a href="#" @click.prevent="$modal.show(\'theseed-setting\')" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-wrench"></i>
                            </span>&nbsp;
                            스킨 설정
                        </a>
                        <nuxt-link to="/member/mypage" class="navbar-item">
                            <span class="icon">
                                <i class="far fa-user-circle"></i>
                            </span>&nbsp;
                            내정보
                        </nuxt-link>
                        <nuxt-link :to="doc_action_link(user_doc($store.state.session.member.username), \'w\')" class="navbar-item">
                            <span class="icon">
                                <i class="far fa-sticky-note"></i>
                            </span>&nbsp;
                            내 사용자 문서
                        </nuxt-link>
                        <div class="navbar-divider"></div>
                        <nuxt-link :to="contribution_author_link($store.state.session.member.username)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-line"></i>
                            </span>&nbsp;
                            내 문서 기여 목록
                        </nuxt-link>
                        <nuxt-link :to="contribution_author_link_discuss($store.state.session.member.username)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-bar"></i>
                            </span>&nbsp;
                            내 토론 기여 목록
                        </nuxt-link>
                        <nuxt-link to="/member/starred_documents" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-bookmark"></i>
                            </span>&nbsp;
                            별찜한 문서들
                        </nuxt-link>
                        <div class="navbar-divider"></div>
                        <nuxt-link :to="{ path: \'/member/logout\', query: { redirect: $route.fullPath } }" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </span>&nbsp;
                            로그아웃
                        </nuxt-link>
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
                        <nuxt-link :to="contribution_ip_link($store.state.session.ip)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-line"></i>
                            </span>&nbsp;
                            내 문서 기여 목록
                        </nuxt-link>
                        <nuxt-link :to="contribution_ip_link_discuss($store.state.session.ip)" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-chart-bar"></i>
                            </span>&nbsp;
                            내 토론 기여 목록
                        </nuxt-link>
                        <div class="navbar-divider"></div>
                        <nuxt-link :to="{ path: \'/member/login\', query: { redirect: $route.fullPath } }" class="navbar-item">
                            <span class="icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </span>&nbsp;
                            로그인
                        </nuxt-link>
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
    <section
        id="wiki-main-title"
        class="hero"
        v-bind:class="{ [$store.state.page.data.error || $store.state.page.viewName == \'notfound\' ? \'is-danger\' : \'is-primary\']: true }"
    >
        <div class="hero-body">
            <div class="container">
                <h1 class="title" v-text="$store.state.page.title"></h1>
                <h2 class="subtitle">
                    <span v-if="$store.state.page.viewName === \'wiki\' && $store.state.page.data.date">
                        <local-date :date="$store.state.page.data.date"></local-date>에 마지막으로 수정됐습니다.
                    </span>

                    <span v-else-if="$store.state.page.viewName === \'notfound\'"> 존재하지 않는 문서입니다. 직접 자유롭게 기여해보세요! </span>

                    <span v-else> Powered by the seed engine </span>
                </h2>
            </div>
        </div>
        <div class="hero-foot">
                <nav class="tabs is-left is-boxed" id="wiki-article-menu">
            <div class="container">
                    <ul v-if="$store.state.page.data.document">
                        <li v-bind:class="{ \'is-active\': $store.state.page.viewName === \'wiki\' }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'w\')">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 읽기</span>
                            </nuxt-link>
                        </li>
                        <li v-bind:class="{ \'is-active\': $store.state.page.viewName === \'edit\' }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'edit\')" class="edit-anchor">
                                <span class="icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 편집</span>
                            </nuxt-link>
                        </li>
                        <li v-bind:class="{ \'is-active\': [\'thread\', \'thread_list\', \'thread_list_close\'].includes($store.state.page.viewName) }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'discuss\')">
                                <span class="icon">
                                    <i class="far fa-comments"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 토론</span>
                            </nuxt-link>
                        </li>
                        <li v-bind:class="{ \'is-active\': $store.state.page.viewName === \'move\' }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'move\')">
                                <span class="icon">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 이동</span>
                            </nuxt-link>
                        </li>
                        <li v-bind:class="{ \'is-active\': $store.state.page.viewName === \'delete\' }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'delete\')">
                                <span class="icon">
                                    <i class="far fa-trash-alt"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 삭제</span>
                            </nuxt-link>
                        </li>
                        <li v-bind:class="{ \'is-active\': $store.state.page.viewName === \'backlink\' }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'backlink\')">
                                <span class="icon">
                                    <i class="fas fa-random"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 역링크</span>
                            </nuxt-link>
                        </li>
                        <li v-bind:class="{ \'is-active\': $store.state.page.viewName === \'history\' }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'history\')">
                                <span class="icon">
                                    <i class="fas fa-history"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 역사</span>
                            </nuxt-link>
                        </li>
                        <li v-bind:class="{ \'is-active\': $store.state.page.viewName === \'acl\' }">
                            <nuxt-link :to="doc_action_link($store.state.page.data.document, \'acl\')">
                                <span class="icon">
                                    <i class="fas fa-key"></i>
                                </span>
                                <span class="wiki-article-menu-text"> ACL</span>
                            </nuxt-link>
                        </li>

                        <!-- [나무위키] main.b9faec2b37c8d51a1d7e.js 참고함 -->
                        <li
                            v-if="$store.state.page.viewName === \'wiki\'"
                            class="star-tab"
                            v-bind:class="{ starred: $store.state.page.data.starred }"
                        >
                            <nuxt-link
                                v-if="$store.state.page.data.starred"
                                :to="doc_action_link($store.state.page.data.document, \'member/unstar\')"
                            >
                                <span class="icon">
                                    <i class="fas fa-star"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 별찜 해제 (</span
                                ><span class="star-count">{{ $store.state.page.data.star_count ? $store.state.page.data.star_count : \'\' }}</span
                                ><span class="wiki-article-menu-text">)</span>
                            </nuxt-link>
                            <nuxt-link v-else :to="doc_action_link($store.state.page.data.document, \'member/star\')">
                                <span class="icon">
                                    <i class="fas fa-star"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 별찜 (</span
                                ><span class="star-count">{{ $store.state.page.data.star_count ? $store.state.page.data.star_count : \'\' }}</span
                                ><span class="wiki-article-menu-text">)</span>
                            </nuxt-link>
                        </li>

                        <!-- [나무위키] main.a65ef46d6b3879416d5f.js 및 main.b9faec2b37c8d51a1d7e.js 참고함 -->
                        <li v-if="$store.state.page.data.user">
                            <nuxt-link :to="contribution_author_link($store.state.page.data.document.title)">
                                <span class="icon">
                                    <i class="fas fas fa-chart-line"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 기여내역</span>
                            </nuxt-link>
                        </li>
                    </ul>

                    <ul v-else>
                        <li class="is-active">
                            <a href="#">
                                <span class="icon">
                                    <i class="fas fa-cogs"></i>
                                </span>
                                <span class="wiki-article-menu-text"> 특수문서</span>
                            </a>
                        </li>
                    </ul>
            </div>
                </nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <b-notification
                v-if="
                    $store.state.session.member &&
                    $store.state.session.member.user_document_discuss &&
                    $store.state.localConfig[\'wiki.hide_user_document_discuss\'] !== $store.state.session.member.user_document_discuss
                "
                class="is-link"
            >
                <nuxt-link :to="doc_action_link(user_doc($store.state.session.member.username), \'discuss\')">사용자 토론</nuxt-link>이 있습니다.
                확인해주세요.
            </b-notification>

            <b-notification v-if="$store.state.config[\'wiki.sitenotice\']">
                <span v-html="$store.state.config[\'wiki.sitenotice\']"></span>
            </b-notification>

            <div class="wiki-article content" @dblclick="doBehaviorWhenDblClick">
                ';
		echo LR\Filters::escapeHtmlText($wiki->skin->inner_layout) /* line 343 */;
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

                    theseed-skin-buma by LiteHell, the seed engine by theseed.io
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
@import \'./css/bulma.min.css\';
@import \'./css/layout.css\';
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
			foreach (array_intersect_key(['m' => '87'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
