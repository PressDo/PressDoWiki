<?php

use Latte\Runtime as LR;

/** source: /home/pi/phs/pressdo/skins/liberty/layout.latte */
final class Template10e9e9a012 extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<br>
<div id="top"></div>
<div class="nav-wrapper navbar-fixed-top">
    <nav class="navbar navbar-dark">
        <a class="navbar-brand" href="/">PressDoWiki</a>
        <ul class="nav navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="/RecentChanges"><span class="fa fa-refresh"></span><span class="hide-title">';
		echo LR\Filters::escapeHtmlText($lang['menu']['RecentChanges']) /* line 8 */;
		echo '</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/RecentDiscuss"><span class="fa fa-comments"></span><span class="hide-title">';
		echo LR\Filters::escapeHtmlText($lang['menu']['RecentDiscuss']) /* line 11 */;
		echo '</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/random"><span class="fa fa-random"></span><span class="hide-title">';
		echo LR\Filters::escapeHtmlText($lang['menu']['random']) /* line 14 */;
		echo '</span></a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle dropdown-toggle-fix" href="#" data-toggle="dropdown" aria-expanded="false">
                    <span class="fa fa-gear"></span><span class="hide-title">';
		echo LR\Filters::escapeHtmlText($lang['menu']['tools']) /* line 18 */;
		echo '</span>
                </a>
                <div class="dropdown-menu" role="menu">
                    <a href="/NeededPages" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['NeededPages']) /* line 21 */;
		echo '</a>
                    <a href="/OrphanedPages" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['OrphanedPages']) /* line 22 */;
		echo '</a>
                    <a href="/OldPages" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['OldPages']) /* line 23 */;
		echo '</a>
                    <a href="/ShortestPages" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['ShortestPages']) /* line 24 */;
		echo '</a>
                    <a href="/LongestPages" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['LongestPages']) /* line 25 */;
		echo '</a>
                    <a href="/BlockHistory" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['BlockHistory']) /* line 26 */;
		echo '</a>
                    <a href="/RandomPage" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['RandomPage']) /* line 27 */;
		echo '</a>
                    <a href="/Upload" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['Upload']) /* line 28 */;
		echo '</a>
                    <a href="/License" class="dropdown-item">';
		echo LR\Filters::escapeHtmlText($lang['page']['License']) /* line 29 */;
		echo '</a>
                    <a to="#" @click.prevent="$modal.show(\'theseed-setting\');" class="dropdown-item">설정</a>
';
		if (count($wiki['session']['menus']) > 0) /* line 31 */ {
			$iterations = 0;
			foreach ($wiki['session']['menus'] as $m) /* line 32 */ {
				echo '                            <a href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($m['l'])) /* line 32 */;
				echo '" class="dropdown-item">';
				echo LR\Filters::escapeHtmlText($m['t']) /* line 32 */;
				echo '</a>
';
				$iterations++;
			}
		}
		echo '                </div>
            </li>
        </ul>
        <div class="navbar-login">
';
		if ($wiki['session']['member']) /* line 38 */ {
			echo '                <div class="dropdown login-menu">
                    <a class="dropdown-toggle" type="button" id="login-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="profile-img" src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['session']['member']['gravatar_url'])) /* line 41 */;
			echo '">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right login-dropdown-menu" aria-labelledby="login-menu">
                        <a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($namespace['user'].':'.$wiki['session']['member']['username']))) /* line 44 */;
			echo '" class="dropdown-item">';
			echo LR\Filters::escapeHtmlText($wiki['session']['member']['username']) /* line 44 */;
			echo '</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/contribution/author/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['session']['member']['username'])) /* line 46 */;
			echo '/document">';
			echo LR\Filters::escapeHtmlText($lang['page']['contribution_document']) /* line 46 */;
			echo '</a>
                        <a class="dropdown-item" href="/contribution/author/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki['session']['member']['username'])) /* line 47 */;
			echo '/discuss">';
			echo LR\Filters::escapeHtmlText($lang['page']['contribution_discuss']) /* line 47 */;
			echo '</a>
                        <div class="dropdown-divider"></div>
                        <a href="/member/mypage" class="dropdown-item">';
			echo LR\Filters::escapeHtmlText($lang['page']['mypage']) /* line 49 */;
			echo '</a>
                        <div class="dropdown-divider"></div>
                        <a href="/member/starred_documents" class="dropdown-item">';
			echo LR\Filters::escapeHtmlText($lang['page']['starred_documents']) /* line 51 */;
			echo '</a>
                        <div class="dropdown-divider view-logout"></div>
                        <a href="/member/logout?redirect=$wiki->request_uri" class="dropdown-item view-logout">';
			echo LR\Filters::escapeHtmlText($lang-['auth']['logout']) /* line 53 */;
			echo '</a>
                    </div>
                </div>
                <a href="/member/logout?redirect=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($uri)) /* line 56 */;
			echo '" class="hide-logout logout-btn"><span class="fa fa-sign-out"></span></a>
';
		}
		else /* line 57 */ {
			echo '                <a href="/member/login?redirect=';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($uri)) /* line 58 */;
			echo '" class="none-outline">
                    <span class="fa fa-sign-in"></span>
                </a>
';
		}
		echo '        </div>
        <div id="pt-notifications" class="navbar-notification">
            <a href="#"><span class="label label-danger"></span></a>
        </div>
        <form id="searchform" class="form-inline">
            <div class="input-group">
                <input type="search" name="q" placeholder="검색" accesskey="f" class="form-control" id="searchInput" autocomplete="off">
                <span class="input-group-btn">
                    <button type="button" name="go" value="보기" id="searchGoButton" class="btn btn-secondary" onclick="onClickGo()"><span class="fa fa-eye"></span></button>
                    <button type="button" name="fulltext" value="검색" id="searchSearchButton" class="btn btn-secondary" onclick="onClickSearch()"><span class="fa fa-search"></span></button>
                </span>
            </div>
';
		if ($show) /* line 74 */ {
			echo '            <div class="v-autocomplete-list">
';
			$iterations = 0;
			foreach ($internalItems as $item => $i) /* line 75 */ {
				echo '                <div class="v-autocomplete-list-item" onclick="onClickItem(item)" :class="{\'v-autocomplete-item-active\': i === cursor}" onmouseover="cursor = i">
                    <div>';
				echo LR\Filters::escapeHtmlText($item) /* line 76 */;
				echo '</div>
                </div>
';
				$iterations++;
			}
			echo '            </div>
';
		}
		echo '        </form>
    </nav>
</div>
<div class="content-wrapper">
    <div class="liberty-sidebar">
        <div class="liberty-right-fixed">
            <div class="live-recent">
                <div class="live-recent-header">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="liberty-recent-tab1">최근바뀜</a>
                        </li>
                    </ul>
                </div>
                <div class="live-recent-content">
                    <ul class="live-recent-list" id="live-recent-list">
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        <li><span class="recent-item">&nbsp;</span></li>
                        
                        <li v-for="(r,i) in recent" v-bind:key="i"><nuxt-link class="recent-item" v-bind:key="r.document" :to="doc_action_link(r.document, \'w\')">[<local-date :date="r.date" format="H:i:s"></local-date>] </nuxt-link></li>
                    </ul>
                </div>
                <div class="live-recent-footer">
                    <a href="/RecentChanges" title="최근 변경내역"><span class="label label-info">더보기</span></a> </div>
            </div>
        </div>
    </div>
    <div class="container-fluid liberty-content">
        <div class="liberty-content-header">
';
		if ($wiki['page']['data']['document']) /* line 116 */ {
			echo '            <div class="content-tools">
                <div class="btn-group" role="group" aria-label="content-tools">
                    <a href="/w/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 119 */;
			echo '" class="btn btn-secondary tools-btn">';
			echo LR\Filters::escapeHtmlText($lang['btn']['document']) /* line 119 */;
			echo '</a>
                    <a href="/edit/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 120 */;
			echo '" class="btn btn-secondary tools-btn">';
			echo LR\Filters::escapeHtmlText($lang['btn']['edit']) /* line 120 */;
			echo '</a>
                    <a href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 121 */;
			echo '" class="btn btn-secondary tools-btn">';
			echo LR\Filters::escapeHtmlText($lang['btn']['discuss']) /* line 121 */;
			echo '</a>
                    <a href="/history/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 122 */;
			echo '" class="btn btn-secondary tools-btn">';
			echo LR\Filters::escapeHtmlText($lang['btn']['history']) /* line 122 */;
			echo '</a>
                    <button type="button" class="btn btn-secondary tools-btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" role="menu">
                        <a href="/backlink/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 127 */;
			echo '" class="dropdown-item">';
			echo LR\Filters::escapeHtmlText($lang['btn']['backlink']) /* line 127 */;
			echo '</a>
                        <a href="/delete/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 128 */;
			echo '" class="dropdown-item">';
			echo LR\Filters::escapeHtmlText($lang['btn']['delete']) /* line 128 */;
			echo '</a>
                        <a href="/move/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 129 */;
			echo '" class="dropdown-item">';
			echo LR\Filters::escapeHtmlText($lang['btn']['move']) /* line 129 */;
			echo '</a>
                        <a href="/acl/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 130 */;
			echo '" class="dropdown-item">';
			echo LR\Filters::escapeHtmlText($lang['btn']['ACL']) /* line 130 */;
			echo '</a>
';
			if ($wiki['page']['data']['user']) /* line 131 */ {
				echo '                        <a href="/contribution/author/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['data']['document']['title']))) /* line 131 */;
				echo '/document" class="dropdown-item">';
				echo LR\Filters::escapeHtmlText($lang['page']['contribution']) /* line 131 */;
				echo '</a>
';
			}
			if ($wiki['page']['data']['starred']) /* line 132 */ {
				echo '                            <a href="/member/unstar/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 133 */;
				echo '" class="dropdown-item">
                                <span class="fa fa-star"></span>
                                <span class="star-count">';
				echo LR\Filters::escapeHtmlText($wiki['page']['data']['star_count']) /* line 135 */;
				echo '</span>
                            </a>
';
			}
			else /* line 137 */ {
				echo '                            <a href="/member/star/';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki['page']['full_title']))) /* line 138 */;
				echo '" class="dropdown-item">
                                <span class="fa fa-star-o"></span>
                                <span class="star-count">';
				echo LR\Filters::escapeHtmlText($wiki['page']['data']['star_count']) /* line 140 */;
				echo '</span>
                            </a>
';
			}
			if ($wiki['page']['data']['menus']) /* line 143 */ {
				$iterations = 0;
				foreach ($wiki['page']['data']['menus'] as $m) /* line 144 */ {
					echo '                            <a href="$m[\'to\']" class="dropdown-item">';
					echo LR\Filters::escapeHtmlText($m['title']) /* line 144 */;
					echo '</a>
';
					$iterations++;
				}
			}
			echo '                    </div>
                </div>
            </div>
';
		}
		echo '            <div class="title">
                <h1>
                    ';
		echo LR\Filters::escapeHtmlText($wiki['page']['full_title']) /* line 152 */;
		echo '
                </h1>
            </div>
        </div>
        <div class="liberty-content-main wiki-article">
';
		if ($wiki['session']['member'] && $wiki['session']['member']['user_document_discuss'] && $config['hide_user_document_discuss'] !== $wiki['session']['member']['user_document_discuss']) /* line 157 */ {
			echo '            <div class="alert alert-info fade in" id="userDiscussAlert" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                ';
			echo LR\Filters::escapeHtmlText(($this->filters->replace)($lang['msg']['user_thread_exists'], '@1@', '/discuss/'.rawurlencode($wiki['session']['member']['username']))) /* line 162 */;
			echo '
            </div>
';
		}
		echo '            ';
		echo $innerLayout /* line 164 */;
		echo $innerBlock /* line 164 */;
		echo '
        </div>
        <div class="liberty-footer" id="bottom">
';
		if ($wiki->page->view_name === 'wiki' && $wiki['page']['data']['date']) /* line 167 */ {
			echo '            <ul class="footer-info">
                <li class="footer-info-lastmod">';
			echo ($this->filters->replace)($lang-['document']['lastchanged'], '@1@', '<time datetime="'.date("Y-m-d\TH:i:s", $wiki['page']['data']['date'] - 32400).'.000Z'.'">'.date("Y-m-d H:i:s", $wiki['page']['data']['date']).'</time>') /* line 168 */;
			echo '</li>
                <li class="footer-info-copyright">';
			echo $config['copyright_text'] /* line 169 */;
			echo '</li>
            </ul>
';
		}
		echo '            <ul class="footer-places">
            </ul>
            <ul class="footer-icons">
                <li class="footer-poweredbyico">
                    <a href="//gitlab.com/librewiki/Liberty-MW-Skin">Liberty</a> | <a href="//github.com/PressDo/PressDoWiki/">PressDo</a>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="modal" id="footnoteModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">각주:</h5>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-block" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>
<div class="scroll-buttons">
    <a class="scroll-toc" href="#toc"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
    <a class="scroll-button" href="#top" id="left"><i class="fa fa-arrow-up" aria-hidden="true"></i></a>
    <a class="scroll-bottom" href="#bottom" id="right"><i class="fa fa-arrow-down" aria-hidden="true"></i></a>
</div>';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['m' => '32, 144', 'item' => '75', 'i' => '75'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
