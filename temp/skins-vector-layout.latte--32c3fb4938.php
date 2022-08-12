<?php

use Latte\Runtime as LR;

/** source: /home/pi/phs/pressdo/skins/vector/layout.latte */
final class Template32c3fb4938 extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div id="mw-page-base" class="noprint"></div>
<div id="mw-head-base" class="noprint"></div>
<div id="content" class="mw-body" role="main">
    <a id="top"></a>
';
		if ($wiki->config->sitenotice !== null) /* line 5 */ {
			echo '    <div id="siteNotice" class="mw-body-content">
        ';
			echo LR\Filters::escapeHtmlText($wiki->config->sitenotice) /* line 6 */;
			echo '
    </div>
';
		}
		echo '    <div class="mw-indicators mw-body-content"></div>
    <h1 id="firstHeading" class="firstHeading" lang="ko">';
		echo LR\Filters::escapeHtmlText($wiki->page->full_title) /* line 9 */;
		echo '</h1>
    <div id="bodyContent" class="mw-body-content">
        <div id="siteSub" class="noprint"></div>
        <div id="contentSub">
';
		if ($wiki->page->data->rev && $wiki->page->view_name == 'wiki') /* line 13 */ {
			echo '            <div class="mw-revision">
                <div id="mw-revision-info">{ date | to_date | localdate(\'Y-m-d H:i:sO\') }에 작성된 r{ rev } 판</div>
                <!-- <div id="mv-revision-nav"></div> -->
            </div>
';
		}
		echo '        </div>
';
		if ($wiki->session->member && $wiki->session->member->user_document_discuss && $wiki->config->hide_user_document_discuss !== $wiki->session->member->user_document_discuss) /* line 18 */ {
			echo '        <div class="usermessage theseed-user-document-discuss-alert">현재 진행중인
            <a href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->session->member->username))) /* line 19 */;
			echo '">사용자토론</a>이 있습니다.</div>
';
		}
		echo '        <div id="mw-content-text" class="mw-content-ltr wiki-article" dir="ltr">
            ';
		echo $wiki->skin->inner_layout /* line 21 */;
		echo '
        </div>
        <div class="visualClear"></div>
    </div>
</div>
<div id="mw-navigation">
    <h2>둘러보기 메뉴</h2>
    <div id="mw-head">
        <div id="p-personal" role="navigation" aria-labelledby="p-personal-label">
            <h3 id="p-personal-label">개인 도구</h3>
            <ul>
';
		if ($wiki->session->member) /* line 32 */ {
			echo '                <li id="pt-userpage">
                    <a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->session->member->username))) /* line 34 */;
			echo '" title="내 사용자 문서 [Alt+Shift+.]" accesskey=".">';
			echo LR\Filters::escapeHtmlText($wiki->session->member->username) /* line 34 */;
			echo '</a>
                </li>
                <li id="pt-mytalk">
                    <a href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->session->member->username))) /* line 37 */;
			echo '" title="내 토론 [Alt+Shift+n]" accesskey="n">토론</a>
                </li>
                <li id="pt-preferences">
                    <a href="/member/mypage" title="계정설정">계정 설정</a>
                </li>
                <li id="pt-mycontris">
                    <a href="/contribution/author/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->session->member->username))) /* line 43 */;
			echo '/document" title="내 기여 목록 [Alt+Shift+y]" accesskey="y">기여</a>
                </li>
                <li id="pt-watchlist">
                    <a href="/member/starred_documents" title="주시문서 목록 [Alt+Shift+l]" accesskey="l">주시문서 목록</a>
                </li>
                <li id="pt-logout">
                    <a href="/member/logout?redirect=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->request_uri)) /* line 49 */;
			echo '" title="로그아웃">로그아웃</a>
                </li>
';
		}
		else /* line 51 */ {
			echo '                <li id="pt-anonuserpage">로그인하지 않음</li>
                <li id="pt-anoncontribs">
                    <a href="/contribution/ip/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->session->ip)) /* line 54 */;
			echo '/document" title="이 IP 주소의 편집 목록 [Alt+Shift+y]" accesskey="y">기여</a>
                </li>
                <li id="pt-createaccount">
                    <a href="/member/signup?redirect=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->request_uri)) /* line 57 */;
			echo '" title="계정을 만들고 로그인하는 것이 좋습니다; 하지만, 필수는 아닙니다">계정 만들기</a>
                </li>
                <li id="pt-login">
                    <a href="/member/login?redirect=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->request_uri)) /* line 60 */;
			echo '" title="';
			echo LR\Filters::escapeHtmlAttr($wiki->config->site_name) /* line 60 */;
			echo '에 로그인하면 여러가지 편리한 기능을 사용할 수 있습니다. [Alt+Shift+o]" accesskey="o">로그인</a>
                </li>
';
		}
		echo '            </ul>
        </div>
        <div id="left-navigation">
            <div id="p-namespaces" class="vectorTabs" role="navigation" aria-labelledby="p-personal-label">
                <h3 id="p-namespaces-label">이름공간</h3>
                <ul>
';
		if ($wiki->page->data->document) /* line 69 */ {
			echo '                    <li';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name == 'wiki' ? 'selected' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 70 */;
			echo '>
                        <span>
                            <a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 72 */;
			echo '">문서</a>
                        </span>
                    </li>
                    <li';
			echo ($ʟ_tmp = array_filter([strpos('thread', $wiki->page->view_name) === 0 ? 'selected' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 75 */;
			echo '>
                        <span>
                            <a href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 77 */;
			echo '">토론</a>
                        </span>
                    </li>
';
			if ($wiki->page->data->user) /* line 80 */ {
				echo '                    <li>
                        <span>
                            <a href="/contribution/author/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->data->document->title))) /* line 82 */;
				echo '/document">기여내역</a>
                        </span>
                    </li>
';
			}
		}
		else /* line 85 */ {
			echo '                    <li>
                        <span>
                            <a href="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->request_uri)) /* line 88 */;
			echo '">특수문서</a>
                        </span>
                    </li>
';
		}
		echo '                </ul>
            </div>
        </div>
        <div id="right-navigation">
';
		if ($wiki->page->data->document) /* line 96 */ {
			echo '            <div id="p-views" role="navigation" class="vectorTabs" aria-labelledby="p-views-label">
                <h3 id="p-views-label">보기</h3>
                <ul>
                    <li id="ca-view"';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name == 'wiki' ? 'selected' : null, 'collapsible'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 100 */;
			echo '>
                        <span>
                            <a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 102 */;
			echo '">읽기</a>
                        </span>
                    </li>
                    <li id="ca-edit"';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name == 'edit' ? 'selected' : null, 'collapsible'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 105 */;
			echo '>
                        <span>
                            <a href="/edit/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 107 */;
			echo '" title="이 문서 편집하기 [Alt+Shift+e]" accesskey="e">편집</a>
                        </span>
                    </li>
                    <li id="ca-history"';
			echo ($ʟ_tmp = array_filter([$wiki->page->view_name == 'history' ? 'selected' : null, 'collapsible'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 110 */;
			echo '>
                        <span>
                            <a href="/history/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 112 */;
			echo '" title="이 문서의 과거 편집 내역입니다. [Alt+Shift+h]" accesskey="h">역사 보기</a>
                        </span>
                    </li>
                    <!-- 원본은 주시문서 설정시 빙글빙글 돌아가는 등의 애니메이션이 있지만 귀찮아서 구현 안함. -->
';
			if ($wiki->page->data->starred) /* line 116 */ {
				echo '                    <li id="ca-unwatch" class="collapsible icon mw-watchlink">
                        <span>
                            <a href="/member/unstar/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 119 */;
				echo '" data-mw="interface" title="이 문서를 주시문서 목록에 추가 [Alt+Shift+w] (';
				echo LR\Filters::escapeHtmlAttr($wiki->page->data->star_count) /* line 119 */;
				echo '명이 주시중)" accesskey="w">주시 해제</a>
                        </span>
                    </li>
';
			}
			else /* line 122 */ {
				echo '                    <li id="ca-watch" class="collapsible icon mw-watchlink">
                        <span>
                            <a href="/member/star/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 125 */;
				echo '" data-mw="interface" title="이 문서를 주시문서 목록에 추가 [Alt+Shift+w] (';
				echo LR\Filters::escapeHtmlAttr($wiki->page->data->star_count) /* line 125 */;
				echo '명이 주시중)" accesskey="w">주시</a>
                        </span>
                    </li>
';
			}
			echo '                    <span class="placeholder" style="display: none;"></span>
                    <span class="placeholder" style="display: none;"></span>
                </ul>
            </div>
            <div id="p-cactions" role="navigation" class="vectorMenu" aria-labelledby="p-cactions-label">
                <h3 id="p-cactions-label" tabindex="0" style="width: 74px;">
                    <span>더 보기</span>
                </h3>
                <div class="menu">
                    <ul>
                        <li class="collapsible" style="display: list-item;">
                            <span>
                                <a href="/delete/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 141 */;
			echo '" title="이 문서를 삭제합니다.">삭제</a>
                            </span>
                        </li>
                        <li class="collapsible" style="display: list-item;">
                            <span>
                                <a href="/move/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 146 */;
			echo '" title="이 문서를 이동합니다.">이동</a>
                            </span>
                        </li>
                        <li class="collapsible" style="display: list-item;">
                            <span>
                                <a href="/acl/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 151 */;
			echo '" title="ACL을 봅니다.">ACL</a>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
';
		}
		echo '            <div id="p-search" role="search">
                <h3>
                    <label for="searchInput">검색</label>
                </h3>
                <form id="searchform">
                    <div id="simpleSearch">
                        <input name="search" placeholder="';
		echo LR\Filters::escapeHtmlAttr($wiki->config->site_name) /* line 164 */;
		echo ' 검색" title="';
		echo LR\Filters::escapeHtmlAttr($wiki->config->site_name) /* line 164 */;
		echo ' 검색 [Alt+Shift+f]" accesskey="f" id="searchInput" tabindex="1" autocomplete="off"
                            type="search">
                        <input name="go" value="보기" title="이 이름의 문서가 존재하면 그 문서로 바로 가기" id="searchButton" class="searchButton" type="submit"> </div>
                </form>
            </div>
        </div>
    </div>
    <div id="mw-panel">
        <div id="p-logo" role="banner">
            <a class="mw-wiki-logo" href="/" title="대문으로 가기"></a>
        </div>
        <div class="portal" role="navigation" id="p-navigation" aria-labelledby="p-navigation-label">
            <h3 id="p-navigation-label">둘러보기</h3>
            <div class="body">
                <ul>
                    <li>
                        <a href="/" title="대문으로 가기 [Alt+Shift+z]" accesskey="z">대문</a>
                    </li>
                    <li>
                        <a href="/RecentChanges" title="위키의 최근 바뀐 목록 [Alt+Shift+r]" accesskey="r">최근 바뀜</a>
                    </li>
                    <li>
                        <a href="/RecentDiscuss" title="위키의 최근 토론 목록">최근 토론</a>
                    </li>
                    <li>
                        <a href="/random" title="임의 문서 불러오기 [Alt+Shift+x]" accesskey="x">임의 문서로</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="portal" role="navigation" id="p-tb" aria-labelledby="p-tb-label">
            <h3 id="p-tb-label">도구</h3>
            <div class="body">
                <ul>
                    <li>
                        <a href="/backlink/';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 199 */;
		echo '" title="여기를 가리키는 모든 위키 문서의 목록 [Alt+Shift+j]" accesskey="j">여기를 가리키는 문서</a>
                    </li>
';
		if ($wiki->page->data->user) /* line 201 */ {
			echo '                    <li>
                        <a href="/contribution/author/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->data->document->title))) /* line 202 */;
			echo '/document" title="이 사용자의 기여 목록">사용자 기여</a>
                    </li>
';
		}
		echo '                    <li>
                        <a href="/Upload" title="파일 올리기 [Alt+Shift+u]" accesskey="u">파일 올리기</a>
                    </li>
                    <li>
                        <a href="/NeededPages" title="필요한 문서">필요한 문서</a>
                    </li>
                    <li>
                        <a href="/OrphanedPages" title="고립된 문서">고립된 문서</a>
                    </li>
                    <li>
                        <a href="/OldPages" title="방치된 문서">방치된 문서</a>
                    </li>
                    <li>
                        <a href="/ShortestPages" title="내용이 짧은 문서">내용이 짧은 문서</a>
                    </li>
                    <li>
                        <a href="/LongestPages" title="내용이 긴 문서">내용이 긴 문서</a>
                    </li>
                    <li>
                        <a href="/BlockHistory" title="차단 기록">차단 기록</a>
                    </li>
                    <li>
                        <a href="/RandomPage" title="임의 문서들">임의 문서들</a>
                    </li>
                    <li>
                        <a href="/License" title="라이선스">라이선스</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div id="footer" role="contentinfo">
';
		if ($wiki->page->view_name == 'wiki' && $wiki->page->data->date) /* line 236 */ {
			echo '        <ul id="footer-info">
            <li id="footer-info-lastmod">';
			echo ($this->filters->replace)($wiki->lang->document->lastchanged, '@1@', '<time datetime="'.date("Y-m-d\TH:i:s", $wiki->page->data->date - 32400).'.000Z'.'">'.date("Y-m-d H:i:s", $wiki->page->data->date).'</time>') /* line 237 */;
			echo '</li>
            <li id="footer-info-copyright">';
			echo $wiki->config->copyright_text /* line 238 */;
			echo '</li>
        </ul>
';
		}
		echo '        <ul id="footer-places">
            <li>
                <a href="/License" class="extiw">엔진 라이선스</a>
            </li>
        </ul>
        <ul id="footer-icons" class="noprint">
            <li id="footer-poweredbyico">
                <a href="//github.com/PressDo/PressDoWiki/">Powered by PressDo</a>
            </li>
        </ul>
    </div>
</div>
<style>
    .mw-wiki-logo {
        background-image: url("';
		echo LR\Filters::escapeCss($wiki->config->logo_url) /* line 254 */;
		echo '");
    }
</style>';
		return get_defined_vars();
	}

}
