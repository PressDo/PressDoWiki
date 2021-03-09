<?php
/* PressDo Skin 제작 요령
 * namespace를 PressDo로 설정
 * class를 WikiSkin으로 설정
 * header() 기능을 통해 <head> 요소 구현
 * page_frame() 기능을 통해 공통 UI 구현
 * main() 기능을 통해 본문 구현
 * footer() 기능을 통해 하단 메뉴 구현
 * FullPage() 기능을 통해 문서 페이지 셋 구성
 */
namespace PressDo {
    require_once 'PressDoLib.php';
    class WikiSkin {
        public static function header($DocNm) {
            // <head>
            global $conf;
            include 'html/head.html';
            ?><title> <?= $DocNm ?> - <?=$conf['Name'] ?> </title> <?php
        }

        public static function page_frame(){
            // 공통 UI
            ?><style>
@media screen and (max-width:798px) {
a[pressdo-navitem-nonlist] span[ionicon], a[pressdo-navitem-listdown] span[ionicon]{text-align:center; margin:0 .25rem;}
span[nav-text]{display:none;}
}
@media screen and (max-width: 1023px) {
nav {padding:0 .5rem; }
}
@media screen and (min-width: 544px) {
div[nav-cover] {border-radius:0;}
nav {border-radius:.25rem;}
}
* {outline:none;}
*, ::after, ::before {box-sizing:inherit; }
 body { font-size:.95rem; line-height: 1.5; color:#373a3c; }
body, h1, h2, h3, h4, h5, h6 { font-family: 'Nanum Gothic', 'KoPubDotum', 'Noto Sans Korean', 'Noto Sans', 'Malgun Gothic', '맑은 고딕', 'sans-serif'; margin: 0; }
article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {display:block;}
[role="button"], a, area, button, input, label, select, summary, textarea{touch-action:manipulation;}
ol,p,ul{margin:0; padding:0;}
dl,ol,ul{margin-top:0;}
address, dl, ol, ul{margin-bottom:1rem;}
ul{list-style-image: url(data:image/svg+xml;charaet=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPSc1JyDozWlnaHQ9JzEzJz48Y2lyY2xlIGN4PScyLjUnIGN5PSc5LjUnIHI9JzIuNScgZmlsbD0nIzMJM2EzYycvPjwvc3ZnPg==);}

a{color:#0275d8; text-decoration:none; background-color:transparent;}
body, div[pressdo-cover]{ background-color:#f5f5f5;}
div[nav-cover] { min-height: 2.8rem; background-color:#4188f1; z-index:1001; box-shadow:0 2px 4px 0 rgba(0,0,0,.16), 0 2px 10px 0 rgba(0,0,0,.12);
position:absolute; top:0; right:0; left:0;}
nav { padding:0 1rem; border:0; border-radius:0; position:relative; }
div[pressdo-content], nav {max-width:1200px, margin:0 auto;}
a[pressdo-logo]{height:2.8rem; width:6.6rem; padding:0; margin:0; float:left; font-size:1.25rem;}
a[pressdo-logo], a[pressdo-logo]:focus, a[pressdo-logo]:hover{color:#fff}
ul[nav-container]{padding-left:0; margin-bottom:0; list-style:none!important;}
li[pressdo-navitem-nonlist], li[pressdo-navitem-listdown]{margin:0; float:left;}
li[pressdo-navitem-listdown]{position:relative;}
a[pressdo-navitem-listdown]::after{margin-right:0; margin-left:.35rem; display:inline-block; width:0; height:0; vertical-align:middle; content:""; border-top: .3em solid; border-right: .3em solid transparent; border-left: .3em solid transparent;}
a[pressdo-navitem-nonlist], a[pressdo-navitem-listdown]{color:#fff; font-size:1rem; padding: .7rem .8rem; line-height:1.4rem; display:block;}
a[pressdo-navitem-nonlist] span[ionicon], a[pressdo-navitem-listdown] span[ionicon]{font-size:1rem; margin-right:.5rem;}
span[ionicon]{display:inline-block; font: normal normal normal 14px/1 'Ionicons'; font-size:inherit; text-rendering:auto; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;}
div[pressdo-navfunc]{position:absolute; top:100%; left:0; z-index:1000; display:none; float:left; min-width:160px; padding: 5px 0; margin: 2px 0 0; color:#373a3c; text-align:left; list-style:none; background-color:#fff; -webkit-background-clip: padding-box; background-clip:padding-box; border:1px solid rgba(0,0,0,.15); border-radius:.25rem; margin-top:0; border-top-left-radius:0; border-top-right-radius:0; font-size:.95rem; box-shadow:0 6px 12px rgba(0,0,0,.175); border-color:#ele8ed;}
div[pressdo-navfunc] a[pressdo-navfunc-item]{ display:block; width:100%; padding:3px 20px; clear:both; font-weight:400; line-height:1.5; color:#373a3c; text-align:inherit; white-space: nowrap; background: 0,0; border:0;}
*[ion-compass]:before {
    content: "\f37c";
}

*[ion-discuss]:before {
    content: "\f2d4";
}

*[ion-func]:before {
    content: "\f318";
}

*[ion-dropdown]:before {
    content: "\f35f";
}

*[ion-rand]:before {
    content: "\f4a8";
}

*[ion-search]:before {
    content: "\f2f5";
    min-width: 15px;
    display: block;
}

*[ion-move]:before {
    content: "\f3d6";
    min-width: 15px;
    display: block;
}

*[ion-unlogined]:before {
    content: "\f47e";
}
              </style>
        <div pressdo-cover>
            <div nav-cover>
                <nav>
                    <a href="/" pressdo-logo><?=$conf['TitleText']?></a>
                    <ul nav-container>
                        <li pressdo-navitem-nonlist>
                            <a href="/RecentChanges" pressdo-navitem-nonlist title="최근 변경">
                                <span ionicon ion-compass></span>
                                <span nav-text>최근 변경</span>
                            </a>
                        </li>
                        <li pressdo-navitem-listdown>
                            <a href="/RecentDiscuss" pressdo-navitem-nonlist title="최근 토론">
                                <span ionicon ion-discuss></span>
                                <span nav-text>최근 토론</span>
                            </a>
                        </li>
                        <li pressdo-navitem-listdown>
                            <a href="#" pressdo-navitem-listdown title="특수 기능">
                                <span ionicon ion-func></span>
                                <span nav-text>특수 기능</span>
                                <span ionicon ion-dropdown></span>
                            </a>
                            <div pressdo-navfunc role="menu">
                                <a href="//board.namu.wiki/" title="게시판" data-v-193fc2b2="" data-v-b986d46e="" data-v-3e5e2b49="">
                                    <span class="i ion-ios-clipboard" data-v-193fc2b2=""></span> 
                                    <span class="t" data-v-193fc2b2="">게시판</span>
                                </a> 
                                <div pressdo-crossline></div> 
                                <a href="/NeededPages" title="작성이 필요한 문서" data-v-193fc2b2="" data-v-b986d46e="" data-v-3e5e2b49="">
                                    <span class="i ion-md-alert" data-v-193fc2b2=""></span> 
                                    <span class="t" data-v-193fc2b2="">작성이 필요한 문서</span>
                                </a>
                            </div>
                        </li>
                        <li pressdo-navitem-nonlist>
                            <a href="/random" pressdo-navitem-nonlist title="무작위">
                                <span ionicon ion-rand></span>
                                <span nav-text>무작위</span>
                            </a>
                        </li>
                    </ul>
                    <ul pressdo-usermenu>
                        <li pressdo-usermenu pressdo-usermenu-unlogined>
                <?php if(isset($_SESSION['userid'])){ ?>
                    <a href="#" title="Profile" pressdo-usermenu>
                        <img pressdo-usermenu src=<?=$_SESSION['pfpic']?> alt=<?=$_SESSION['userid']?>>
                    </a><?php
                }else{ ?>
                    <a href="#" title="Profile" pressdo-usermenu>
                        <span ionicon ion-unlogined pressdo-usermenu-unlogined></span>
                    </a><?php
                } ?>
                <div pressdo-usermenu-down>
                    <div pressdo-usermenu-down-lv1>
                        <div pressdo-umd1><?=$_SESSION['userid']?></div> 
                        <div pressdo-umd1><?=$_SESSION['usergroup']?></div>
                    </div> 
                    <div pressdo-crossline></div> 
                    <a href="#" title="설정" pressdo-umd2>설정</a> <!----> 
                    <div pressdo-crossline></div> 
                <?php if(isset($_SESSION['userid'])){ ?>
                    <a href="/member/mypage" title="내 정보" pressdo-umd2>내 정보</a> 
                    <a href="/w/사용자:<?=$_SESSION['userid']?>" title="내 사용자 문서" pressdo-umd2>내 사용자 문서</a> 
                    <a href="/member/starred_documents" title="내 문서함" pressdo-umd2>내 문서함</a> 
                    <div pressdo-crossline></div> 
                <?php } ?>
                    <a href="/contribution/author/<?=$_SESSION['userid']?>/document" title="내 문서 기여 목록" pressdo-umd3>내 문서 기여 목록</a> 
                    <a href="/contribution/author/<?=$_SESSION['userid']?>/discuss" title="내 토론 기여 목록" pressdo-umd3>내 토론 기여 목록</a> 
                    <div pressdo-crossline></div> 
                    <?php if(isset($_SESSION['userid'])){ ?>
                    <a href="/member/logout?redirect=" title="로그아웃" pressdo-umd3>로그아웃</a>
                <?php }else{ ?>
                    <a href="/member/login=" title="로그인" pressdo-umd3>로그인</a>
                <?php } ?>
                </div>
                    </li>
                    </ul>
                    <form>
                        <div pressdo-search-form>
                            <span pressdo-sb><a href="/random" pressdo-sb><span ionicon ion-rand></span></a></span>
                            <div sb-wrap><input type="search" name="keyword" placeholder="Search" tabindex="1" pressdo-search autocomplete="off"></div>
                            <span pressdo-sb>
                                <button type="button" onclick="search();" pressdo-sb>
                                    <span ionicon ion-search></span>
                                </button>
                                <button type="button" onclick="godirectly();" pressdo-sb>
                                    <span ionicon ion-move></span>
                                </button>
                            </span>
                        </div>
                    </form>
                </nav>
            </div>
            <section>
            <aside>
                <div>
                    편집 내역
                </div>
            </aside>
            <?php
        }

        public static function main($Doc, $action = 'view')
        {
            // 내용 영역
            global $conf;
            self::page_frame();
            
            switch ($action) {
                case 'view':
                    ///////////////////////// 읽기모드 ////////////////////////////////
                    break;
                case 'edit':
                ///////////////////////// 편집 ////////////////////////////////
                    break;
                case 'history':
                ///////////////////////// 역사 ////////////////////////////////
                    break;
            }
        }
        
        public static function footer()
        {
        global $conf;?>
        </section>
        <footer>
            <hr>
            <p> ⓒCopyright <?=$conf['CopyRight']?></p>
            <p> <?=$conf['HelpMail']?> | <?=$conf['TermsOfUse']?> | <?=$conf['SecPolicy']?> </p>
            <br>
            <br>
            <br>
            <br>
        </footer>
        </div><?php
        }

        public static function FullPage($DocNm, $action = 'view')
        {
            WikiSkin::header($DocNm);
            WikiSkin::main($DocNm, $action);
        }
    }
}
?>
