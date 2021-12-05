<?php
namespace PressDo
{
    require 'skin/liberty/setting.php';
    require 'skin/skin.php';
    class WikiSkin
    {
        public static function AboveContent(){
            global $conf, $uri, $_liberty, $lang, $_SESSION, $_ns;
            ?><!DOCTYPE html>
              <head>
                <link rel="preload" as="style" href="/skin/liberty/skin.css">
                <link rel="stylesheet" href="/skin/liberty/skin.css">
                <link href="<?=$conf['FaviconURL']?>" rel="SHORTCUT ICON">
                <meta charset="UTF-8">
                <meta name="author" content="PRASEOD-">
                <meta name="title" content="<?=$conf['SiteName']?>">
                <meta name="description" content="<?=$conf['Description']?>">
                <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0 ,user-scalable=no">
                <meta http-equiv="Content-Type" content="text/html;">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta property="og:type" content="website">
                <meta property="og:title" content=<?=$conf['SiteName']?>>
                <meta property="og:description" content="<?=$conf['Description']?>">
                <style> div[nav-cover]{background-color:<?=$_liberty['MainColor']?>;}</style>
                <script async defer src="/src/script/main.js"></script>
            </head>
            <body>
                    <div data-pressdo-cover>
                        <div nav-cover>
                            <nav>
                                <a href="/" data-pressdo-logo><?=$conf['TitleText']?></a>
                                <ul nav-container>
                                    <li data-pressdo-navitem-nonlist>
                                        <a href="<?=$uri['RecentChanges']?>" data-pressdo-navitem-nonlist title="<?=$lang['RecentChanges']?>">
                                            <span fa fa-refresh></span>
                                            <span nav-text><?=$lang['menu:RecentChanges']?></span>
                                        </a>
                                    </li>
                                    <li data-pressdo-navitem-nonlist>
                                        <a href="<?=$uri['RecentDiscuss']?>" data-pressdo-navitem-nonlist title="<?=$lang['RecentDiscuss']?>">
                                            <span fa fa-comments></span>
                                            <span nav-text><?=$lang['menu:RecentDiscuss']?></span>
                                        </a>
                                    </li>
                                    <li data-pressdo-navitem-nonlist>
                                        <a href="<?=$uri['random']?>" data-pressdo-navitem-nonlist title="<?=$lang['random']?>">
                                            <span fa fa-random></span>
                                            <span nav-text><?=$lang['menu:random']?></span>
                                        </a>
                                    </li>
                                    <li data-pressdo-navitem-listdown>
                                        <a id="nav-menu" class="hidden-trigger" data-pressdo-toc-fold=hide data-pressdo-navitem-listdown title="<?=$lang['SpecialFunctions']?>"><span fa fa-gear></span><span nav-text> <?=$lang['menu:tools']?></span></a>
                                        <div data-pressdo-navfunc data-pressdo-toc-fold=hide id="content-nav-menu" role="menu">
                                            <?php foreach($conf['CustomSidebar'] as $cfm){
                                                ?><a href="<?=$cfm['url']?>" title="<?=$cfm['name']?>">
                                                <span class="i <?=$cfm['icon']?>"></span>
                                                <span class="t"><?=$cfm['name']?></span>
                                                </a><?php
                                            } ?>
                                            <div data-pressdo-crossline></div> 
                                            <?php /* <a href="<?=$uri['OrphanedPages']?>" data-pressdo-navfunc-item><?=$lang['OrphanedPages']?></a>
                                            <a href="<?=$uri['NeededPages']?>" data-pressdo-navfunc-item><?=$lang['NeededPages']?></a>*/?>
                                            <a href="<?=$uri['OldPages']?>" data-pressdo-navfunc-item><?=$lang['OldPages']?></a>
                                            <a href="<?=$uri['ShortestPages']?>" data-pressdo-navfunc-item><?=$lang['ShortestPages']?></a>
                                            <a href="<?=$uri['LongestPages']?>" data-pressdo-navfunc-item><?=$lang['LongestPages']?></a>
                                            <a href="<?=$uri['BlockHistory']?>" data-pressdo-navfunc-item><?=$lang['BlockHistory']?></a>
                                            <a href="<?=$uri['RandomPage']?>" data-pressdo-navfunc-item>RandomPage</a>
                                            <a href="<?=$uri['Upload']?>" data-pressdo-navfunc-item><?=$lang['Upload']?></a>
                                            <a href="<?=$uri['License']?>" data-pressdo-navfunc-item><?=$lang['License']?></a>
                                            <a to="#" data-pressdo-navfunc-item>설정</a>
                                            <?php foreach ($_SESSION['menus'] as $m) { ?>
                                            <a href="<?=$m['l']?>" title="<?=$m['t']?>">
                                                <span class="i <?=$m['i']?>"></span> 
                                                <span class="t"><?=$m['t']?></span>
                                            </a><?php } ?>
                                        </div>
                                    </li>
                                </ul>
                                <div data-pressdo-usermenu>
                            <?php if(isset($_SESSION['member']['username'])){ ?>
                                <div data-pressdo-usermenu-profile>
                                    <a id="nav-user" data-pressdo-toc-fold=hide class="hidden-trigger" title="Profile" data-pressdo-usermenu><img data-pressdo-usermenu src=<?=$_SESSION['member']['gravatar_url']?>></a>
                                    <div data-pressdo-navfunc data-pressdo-toc-fold=hide id="content-nav-user" role="menu">
                                        <a href="<?=$uri['wiki'].rawurlencode($_ns['user'].':'.$_SESSION['member']['username'])?>" data-pressdo-navfunc-item><?=$_SESSION['member']['username']?></a>
                                        <div data-pressdo-crossline></div>
                                        <a href="<?=str_replace('@1@', $_SESSION['member']['username'], $uri['contribution_document_m'])?>" data-pressdo-navfunc-item><?=$lang['contribution_document']?></a>
                                        <a href="<?=str_replace('@1@', $_SESSION['member']['username'], $uri['contribution_discuss_m'])?>" data-pressdo-navfunc-item><?=$lang['contribution_discuss']?></a>
                                        <div data-pressdo-crossline></div>
                                        <a href="<?=$uri['mypage']?>" data-pressdo-navfunc-item><?=$lang['mypage']?></a>
                                        <a href="<?=$uri['starred_documents']?>" data-pressdo-navfunc-item><?=$lang['starred_documents']?></a>
                                        <div data-pressdo-crossline></div>
                                        <a href="<?=$uri['logout_r'].base64_encode($_SERVER['REQUEST_URI'])?>" class="dropdown-item view-logout"><?=$lang['logout']?></a>
                                    </div>
                                </div>
                                <a href="<?=$uri['logout_r'].base64_encode($_SERVER['REQUEST_URI'])?>" title="Logout" data-pressdo-usermenu>
                                    <span fa fa-sign-out></span>
                                </a><?php
                            } else { ?>
                                <a href="<?=$uri['login_r'].base64_encode($_SERVER['REQUEST_URI'])?>" title="Login" data-pressdo-usermenu>
                                    <span fa fa-sign-in></span>
                                </a>
                            <?php } ?>
                                </div>
                                <form data-pressdo-search-form action="rd.php">
                                    <div data-pressdo-search-form>
                                        <input type="hidden" name="isSearchBar" value="1"><input type="search" id="search-keyword" name="q" placeholder="<?=$lang['search']?>" tabindex="1" data-pressdo-search autocomplete="off">
                                        <span data-pressdo-sb>
                                            <button type="button" id="sb" goto="<?=$uri['wiki']?>" data-pressdo-sb>
                                                <span fa fa-search></span>
                                            </button>
                                            <button type="button" id="sb" goto="<?=$uri['search']?>" data-pressdo-sb>
                                                <span fa fa-move></span>
                                            </button>
                                        </span>
                                    </div>
                                </form>
                            </nav>
                        </div>
                        <section>
                        <aside>
                            <div>
                                <!--Coming Soon!-->
                            </div>
                        </aside>
                        <div data-pressdo-content> <?php
        }
        public static function BelowContent(){
            global $conf; ?>
                        <ul data-pressdo-footer-places></ul>
                        <ul data-pressdo-footer-icons>
                            <li data-pressdo-footer-poweredby>
                                <a href="//gitlab.com/librewiki/Liberty-MW-Skin">Liberty</a> | 
                                <a href="//github.com/PressDo/PressDoWiki/">PressDo</a>
                            </li>
                        </ul>
                        <?=stripslashes($conf['PageFooter'])?>
                    </footer>
                    </div></body><?php
        }
        public static function ConstPage($pagename, $args){
            self::AboveContent(); 
            SkinTemplate::$pagename($args);
            self::BelowContent();
        }
    }
}
