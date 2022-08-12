<?php

use Latte\Runtime as LR;

/** source: /home/pi/phs/pressdo/skins/senkawa/layout.latte */
final class Templateb18ae668fa extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div id="top"></div>
<div class="nav-wrap">
    <nav>
        <a class="brand-link" href="/"></a>
        <ul class="nav-menu">
            <li class="non-dropdown">
                <a class="nav-link" href="/RecentChanges" title="최근 변경">
                    <span class="i ion-md-compass"></span>
                    <span class="t">최근 변경</span>
                </a>
            </li>
            <li class="non-dropdown">
                <a class="nav-link" href="/RecentDiscuss" title="최근 토론">
                    <span class="i ion-md-text"></span>
                    <span class="t">최근 토론</span>
                </a>
            </li>
            <li class="dropdown">
                <a class="nav-link" href="#" title="특수 기능">
                    <span class="i ion-ios-cube"></span>
                    <span class="t">특수 기능</span>
                </a>
                <div class="dropdown">
                    <a class="dropdown-link" href="https://github.com/PressDo/PressDo-skin-senkawa" title="GitHub">
                        <span class="i ion-md-text"></span>
                        <span class="t">GitHub</span>
                    </a>
                    <div class="crossline"></div>
                    <a class="dropdown-link" href="/NeededPages" title="작성이 필요한 문서">
                        <span class="i ion-md-text"></span>
                        <span class="t">작성이 필요한 문서</span>
                    </a>
                    <a class="dropdown-link" href="/OrphanedPages" title="고립된 문서">
                        <span class="i ion-md-text"></span>
                        <span class="t">고립된 문서</span>
                    </a>
                    <a class="dropdown-link" href="/UncategorizedPages" title="분류가 되지 않은 문서">
                        <span class="i ion-md-text"></span>
                        <span class="t">분류가 되지 않은 문서</span>
                    </a>
                    <a class="dropdown-link" href="/OldPages" title="편집된 지 오래된 문서">
                        <span class="i ion-md-text"></span>
                        <span class="t">편집된 지 오래된 문서</span>
                    </a>
                    <a class="dropdown-link" href="/ShortestPages" title="내용이 짧은 문서">
                        <span class="i ion-md-text"></span>
                        <span class="t">내용이 짧은 문서</span>
                    </a>
                    <a class="dropdown-link" href="/LongestPages" title="내용이 긴 문서">
                        <span class="i ion-md-text"></span>
                        <span class="t">내용이 긴 문서</span>
                    </a>
                    <a class="dropdown-link" href="/BlockHistory" title="차단 내역">
                        <span class="i ion-md-text"></span>
                        <span class="t">차단 내역</span>
                    </a>
                    <a class="dropdown-link" href="/RandomPage" title="RandomPage">
                        <span class="i ion-md-text"></span>
                        <span class="t">RandomPage</span>
                    </a>
                    <a class="dropdown-link" href="/Upload" title="파일 올리기">
                        <span class="i ion-md-text"></span>
                        <span class="t">파일 올리기</span>
                    </a>
                    <a class="dropdown-link" href="/License" title="라이선스">
                        <span class="i ion-md-text"></span>
                        <span class="t">라이선스</span>
                    </a>
                </div>
            </li>
            <li class="non-dropdown">
                <a class="nav-link" href="/" title="최근 변경">
                    <span class="i ion-md-compass"></span>
                    <span class="t">외부 링크</span>
                </a>
        </ul>
        <ul class="r">
            <li class="member">
                <a class="member" title="Member menu" href="#">
                    <span class="ion-ios-person"></span>
                </a>
                <div class="member">
                    <div class="member">
';
		if ($wiki['session']['member']) /* line 84 */ {
			echo '                        <div>';
			echo LR\Filters::escapeHtmlText($wiki['session']['member']['username']) /* line 85 */;
			echo '</div>
                        <div>Member</div>
';
		}
		else /* line 87 */ {
			echo '                        <div>';
			echo LR\Filters::escapeHtmlText($wiki['session']['ip']) /* line 88 */;
			echo '</div>
                        <div>Please login!</div>
';
		}
		echo '                    </div>
                    <div class="crossline"></div>
                    <a class="nav-link" href="#" title="설정">설정</a>
                    <div class="crossline"></div>
';
		if ($wiki['session']['member']) /* line 95 */ {
			echo '                        <a class="nav-link" href="/contribution/ip/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['session']['member']['username'])) /* line 96 */;
			echo '/document" title="내 문서 기여 목록">내 문서 기여 목록</a>
                        <a class="nav-link" href="/contribution/ip/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['session']['member']['username'])) /* line 97 */;
			echo '/discuss" title="내 토론 기여 목록">내 토론 기여 목록</a>
                        <div class="crossline"></div>
                        <a class="nav-link" href="/member/logout?redirect=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($uri)) /* line 99 */;
			echo '" title="로그아웃">로그아웃</a>
';
		}
		else /* line 100 */ {
			echo '                        <a class="nav-link" href="/contribution/ip/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['session']['ip'])) /* line 101 */;
			echo '/document" title="내 문서 기여 목록">내 문서 기여 목록</a>
                        <a class="nav-link" href="/contribution/ip/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['session']['ip'])) /* line 102 */;
			echo '/discuss" title="내 토론 기여 목록">내 토론 기여 목록</a>
                        <div class="crossline"></div>
                        <a class="nav-link" href="/member/login?redirect=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($uri)) /* line 104 */;
			echo '" title="로그인">로그인</a>
';
		}
		echo '                </div>
            </li>
        </ul>
        <form>
            <div class="search">
                <span class="search l">
                    <a class="search" href="/random">
                        <span class="ion-ios-shuffle"></span>
                    </a>
                </span>
                <div class="search">
                    <input class="search" type="search" placeholder="Search" tabindex="1" autocomplete="off" value>
                </div>
                <span class="search r">
                    <button type="button" onclick="search()">
                        <span class="ion-ios-search"></span>
                    </button>
                    <button type="button" onclick="go()">
                        <span class="ion-ios-arrow-round-forward"></span>
                    </button>
                </span>
            </div>
        </form>
    </nav>
</div>';
		return get_defined_vars();
	}

}
