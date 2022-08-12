<?php

use Latte\Runtime as LR;

/** source: /home/pi/phs/pressdo/skins/acme/layout.latte */
final class Template67fef7f417 extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '
    <div class="navbar navbar-default navbar-static-top container">
        <div class="navbar-header">
            <button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/" id="r_logo">
                ';
		echo LR\Filters::escapeHtmlText($wiki->config->sitename) /* line 10 */;
		echo '
            </a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li id="right-search">
                    <form method="post" action="/goto" id="searchform" role="search">
                        <input style="display: inline-block;" class="form-control search" type="search" name="search" placeholder="';
		echo LR\Filters::escapeHtmlAttr($wiki->lang->page->Search) /* line 17 */;
		echo '" id="searchInput" autocomplete="off">
                    </form>                
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-close-others="false" data-delay="0" data-hover="dropdown" data-toggle="dropdown" href="javascript:void(0);">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i>
                        최근
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul role="menu" class="dropdown-menu">
                        <li>
                            <a href="/RecentChanges">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                                ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->RecentChanges) /* line 30 */;
		echo '
                            </a>
                        </li>
                        <li>
                            <a href="/RecentDiscuss">
                                <i class="fa fa-comment" aria-hidden="true"></i>
                                ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->RecentDiscuss) /* line 36 */;
		echo '
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-close-others="false" data-delay="0" data-hover="dropdown" data-toggle="dropdown" href="javascript:void(0);">
                        <i class="fa fa-wrench" aria-hidden="true"></i>
                        도구
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul role="menu" class="dropdown-menu">
                        <li>
                            <a href="/random">
                                <i class="fa fa-random" aria-hidden="true"></i>
                                ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->random) /* line 51 */;
		echo '
                            </a>
                        </li>
                        <li>
                            <a href="/other">
                                <i class="fa fa-wrench" aria-hidden="true"></i>
                                도구
                            </a>
                        </li>
                    </ul>
                </li>    
                <li class="dropdown">
                    <a class="dropdown-toggle" data-close-others="false" data-delay="0" data-hover="dropdown" data-toggle="dropdown" href="javascript:void(0);">
                        <i class="fa fa-align-justify" aria-hidden="true"></i>
                        기타
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul role="menu" class="dropdown-menu">
                        <li>
                            <a href="#">
                                <i class="fa fa-random" aria-hidden="true"></i>
                                ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->skin_settings) /* line 72 */;
		echo '
                            </a>
                        </li>
                        <li>
                            <a href="/random">
                                <i class="fa fa-random" aria-hidden="true"></i>
                                ';
		echo LR\Filters::escapeHtmlText($wiki->lang->menu->random) /* line 78 */;
		echo '
                            </a>
                        </li>
                    </ul>
                </li>   
                <li>
                    <a href="/user">
';
		if ($imp[2][4] != '') /* line 85 */ {
			echo '                            <img src="https://www.gravatar.com/avatar/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($wiki->session->gravatar_url)) /* line 86 */;
			echo '?s=30"> ';
			echo LR\Filters::escapeHtmlText($wiki->session->username) /* line 86 */;
			echo "\n";
		}
		else /* line 87 */ {
			if ($imp[2][2] == 1) /* line 88 */ {
				echo '                                <i class="fa fa-user" aria-hidden="true"></i>
';
			}
			elseif ($imp[2][2] == 0) /* line 90 */ {
				echo '                                <i class="fa fa-user-times" aria-hidden="true"></i>
';
			}
			else /* line 92 */ {
				echo '                                <i class="fa fa-user-secret" aria-hidden="true"></i>
';
			}
			echo '                            ';
			echo LR\Filters::escapeHtmlText($wiki->session->username) /* line 95 */;
			echo "\n";
		}
		echo '                    </a>
                </li>  
            </ul>
        </div>
    </div>
</header>
<section id="body">
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-sm-4">
                    <h1 id="fix_title">
                        ';
		echo LR\Filters::escapeHtmlText($wiki->page->full_title) /* line 109 */;
		echo '
                    </h1>
                </div>
                <div class="col-lg-8 col-sm-8">
                    <ol class="breadcrumb pull-right">   
                        <li style="margin: 0;">
';
		$titleArr = explode('/', $wiki->page->full_title) /* line 115 */;
		$parent = implode('/', array_slice($titleArr, 0, count($titleArr) - 1)) /* line 116 */;
		$ʟ_switch = ($wiki->page->view_name) /* line 117 */;
		if (false) {
		}
		elseif (in_array($ʟ_switch, ['wiki'], true)) /* line 118 */ {
			echo '                                <a class="menu-item" href="/edit/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 119 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->btn->edit) /* line 119 */;
			echo '</a>ㆍ
                                <a class="menu-item" href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 120 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->btn->discuss) /* line 120 */;
			echo '</a>ㆍ
                                <a class="menu-item" href="/history/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 121 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->btn->history) /* line 121 */;
			echo '</a>ㆍ
                                <a class="menu-item" href="/backlink/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 122 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->btn->backlink) /* line 122 */;
			echo '</a>ㆍ
                                <a class="menu-item" href="/acl/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 123 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->btn->ACL) /* line 123 */;
			echo '</a>ㆍ
                                <a class="menu-item" href="/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($parent))) /* line 124 */;
			echo '">상위</a>
';
		}
		elseif (in_array($ʟ_switch, ['edit'], true)) /* line 125 */ {
			echo '                                <a class="menu-item" href="/edit/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 126 */;
			echo '">돌아가기</a>ㆍ
                                <a class="menu-item" href="/discuss/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 127 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->btn->delete) /* line 127 */;
			echo '</a>ㆍ
                                <a class="menu-item" href="/history/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 128 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->btn->move) /* line 128 */;
			echo '</a>ㆍ
                                <a class="menu-item" href="/backlink/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 129 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($wiki->lang->page->Upload) /* line 129 */;
			echo '</a>
';
		}
		elseif (in_array($ʟ_switch, ['discuss'], true)) /* line 130 */ {
		}
		elseif (in_array($ʟ_switch, ['acl'], true)) /* line 131 */ {
			echo '                                <a class="menu-item" href="/edit/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 132 */;
			echo '">문서</a>
';
		}
		elseif (in_array($ʟ_switch, ['history'], true)) /* line 133 */ {
		}
		elseif (in_array($ʟ_switch, ['backlink'], true)) /* line 134 */ {
			echo '                                <a class="menu-item" href="/edit/';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(rawurlencode($wiki->page->full_title))) /* line 135 */;
			echo '">돌아가기</a>
';
		}
		echo '                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div id="fix_data" class="col-md-10 col-md-offset-1 mar-b-30">
                ';
		echo $wiki->skin->inner_layout /* line 146 */;
		echo '
            </div>
        </div>
    </div>
</section>
<div class="scroll-buttons">
    <a class="scroll-toc" href="#toc">
        <i class="fa fa-list-alt" aria-hidden="true"></i>
    </a>
    <a class="scroll-button" href="#">
        <i class="fa fa-arrow-up" aria-hidden="true"></i>
    </a>
    <a class="scroll-bottom" href="#footer">
        <i class="fa fa-arrow-down" aria-hidden="true"></i>
    </a>
</div>
<footer class="footer-small" id="footer">
    <div class="container">
        <div class="row">
            <div class="copyright">
                ';
		echo $wiki->config->copyright_text /* line 166 */;
		echo '
                <span id="left_end" class="pull-right">
                    <a href="https://github.com/2DU/openNAMU">
                        <img src="/views/acme/img/on2.png" alt="opennamu" style="width: 100px;">
                    </a>
                    <a href="/views/acme/list.html">Contributor</a>
                </span>    
            </div>
        </div>
    </div>
</footer>
    ';
		return get_defined_vars();
	}

}
