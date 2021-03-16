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
@import url(//fonts.googleapis.com/earlyaccess/nanumgothic.css);
@font-face {font-family: 'Ionicons'; src: url('/src/fonts/ionicons.woff') format('woff')}
@media screen and (max-width:520px) {
form[pressdo-search-form]{width:100%;}
a[pressdo-navitem-nonlist],a[pressdo-navitem-listdown]{padding: .7rem .3rem!important;}
}
@media screen and (max-width:798px) {
a[pressdo-navitem-nonlist] span[ionicon], a[pressdo-navitem-listdown] span[ionicon]{text-align:center; margin:0 .25rem;}
span[nav-text]{display:none;}
}
@media screen and (max-width: 1023px) {
nav {padding:0 .5rem!important; }
nav>form[pressdo-search-form]{float:left; padding:.25rem 0 .5rem;}
nav>div[pressdo-search-form]{display:table;width:100%;}
div[pressdo-search-form]>input[pressdo-search]{width:100%;}
span[pressdo-sb]{width:1%;}
section{margin-top:5.89rem!important;}
section>div[pressdo-content]{margin-right:auto; padding-bottom:.5rem;}
div[pressdo-toolbar]{float:none; text-align:right; border-bottom:1px solid #e1e8ed; padding: .5rem;}
}
@media screen and (min-width: 544px) {
div[nav-cover] {border-radius:0;}
nav {border-radius:.25rem;}
}
html{box-sizing: border-box;}
* {outline:none;}
*, :after, :before {box-sizing:inherit; }
 body { font-size:.95rem; line-height: 1.5; color:#373a3c; }
body, h1, h2, h3, h4, h5, h6 { font-family: 'Nanum Gothic', 'KoPubDotum M', 'Noto Sans Korean', 'Noto Sans', 'Malgun Gothic', '맑은 고딕', 'sans-serif'; margin: 0; }
article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {display:block;}
[role="button"], a, area, button, input, label, select, summary, textarea{touch-action:manipulation;}
ol,p,ul{margin:0; padding:0;}
dl,ol,ul{margin-top:0;}
address, dl, ol, ul{margin-bottom:1rem;}
button, input, select, textarea{margin:0; line-height:inherit; border-radius:0;}
button, input, optgroup, select, textarea { font:inherit; color:inherit;}
button, select{text-transform:none;}
button{overflow:visible;}
ul{list-style-image: url(data:image/svg+xml;charaet=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPSc1JyDozWlnaHQ9JzEzJz48Y2lyY2xlIGN4PScyLjUnIGN5PSc5LjUnIHI9JzIuNScgZmlsbD0nIzMJM2EzYycvPjwvc3ZnPg==);}
a{color:#0275d8; text-decoration:none; background-color:transparent;}
body, div[pressdo-cover]{ background-color:#f5f5f5;}
div[nav-cover] { min-height: 2.8rem; background-color:#4188f1; z-index:1001; box-shadow:0 2px 4px 0 rgba(0,0,0,.16), 0 2px 10px 0 rgba(0,0,0,.12);
position:absolute; top:0; right:0; left:0;}
nav { padding:0 1rem; border:0; border-radius:0; position:relative; }
div[pressdo-content], nav {max-width:1200px; margin:0 auto;}
a[pressdo-logo]{height:2.8rem; width:6.6rem; padding:0; margin:0; float:left; font-size:1.25rem;}
a[pressdo-logo], a[pressdo-logo]:focus, a[pressdo-logo]:hover{color:#fff}
ul[nav-container]{padding-left:0; margin-bottom:0; list-style:none!important;}
li[pressdo-navitem-nonlist], li[pressdo-navitem-listdown]{margin:0; float:left;}
li[pressdo-navitem-listdown]{position:relative;}
a[pressdo-navitem-listdown]::after{margin-right:0; margin-left:.35rem; display:inline-block; width:0; height:0; vertical-align:middle; border-top: .3em solid; border-right: .3em solid transparent; border-left: .3em solid transparent;}
a[pressdo-navitem-nonlist], a[pressdo-navitem-listdown]{color:#fff; font-size:1rem; padding: .7rem .8rem; line-height:1.4rem; display:block;}
a[pressod-navitem-nonlist]:hover, a[pressdo-navitem-listdown]:hover {background-color: #023e98;-webkit-transition: .3s; transition: .3s;}
a[pressdo-navitem-nonlist] span[ionicon], a[pressdo-navitem-listdown] span[ionicon]{font-size:1rem; margin-right:.5rem;}
span[ionicon]{display:inline-block; font: normal normal normal 14px/1.45 'Ionicons'; font-size:inherit; text-rendering:auto; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;}
div[pressdo-navfunc]{position:absolute; top:100%; left:0; z-index:1000; display:none; float:left; min-width:160px; padding: 5px 0; margin: 2px 0 0; color:#373a3c; text-align:left; list-style:none; background-color:#fff; -webkit-background-clip: padding-box; background-clip:padding-box; border:1px solid rgba(0,0,0,.15); border-radius:.25rem; margin-top:0; border-top-left-radius:0; border-top-right-radius:0; font-size:.95rem; box-shadow:0 6px 12px rgba(0,0,0,.175); border-color:#e1e8ed;}
div[pressdo-navfunc] a[pressdo-navfunc-item]{ display:block; width:100%; padding:3px 20px; clear:both; font-weight:400; line-height:1.5; color:#373a3c; text-align:inherit; white-space: nowrap; background: 0,0; border:0;}
div[pressdo-usermenu]{float:right; padding-left:.8rem;}
form[pressdo-search-form]{padding:.4rem 0; float:right;}
div[pressdo-search-form]{position:relative; display:table; border-collapse:separate; width:100%;}
input[pressdo-search]{font-size:.8rem; height:2rem; width:10.0rem; padding:.2rem .4rem; border-color:#e1e8ed; border-radius:0; border-top-left-radius:.35rem; border-bottom-left-radius:.35rem; display:table-cell; position:relative; z-index:2; float:left; margin-bottom:0; line-height:1.5; color:#55595c; background-color:#fff; background-image:none;border: 1px solid #ccc; }
input[type="search"]{-webkit-appearance:none; box-sizing:inherit;}
span[pressdo-sb], span[pressdo-sb]>button[pressdo-sb]{position:relative;}
span[pressdo-sb]{font-size:0; white-space:nowrap; vertical-align:middle; display:table-cell;}
button[pressdo-sb]{color:#4f5b63; padding:.2rem .4rem; line-height:22px; z-index:3;  margin-left:-1px; position:relative; box-shadow:none; background-color:#fff; border-color:#ccc; display:inline-block; font-size:1rem; font-weight:400; text-align:center; white-space:nowrap; vertical-align:middle; cursor:pointer; user-select: none; border:1px solid #ccc; }
button[pressdo-sb]:hover{background-color: #4188f1;border-color: #5997f3;color: #fff;outline: 0;-webkit-transition: .3s;transition: .3s;}
button[pressdo-sb]:last-of-type{border-top-right-radius: .35rem; border-bottom-right-radius: .35rem;}
section{max-width:1200px; margin:0 auto; margin-top:3.33rem;}
div[pressdo-content]{padding: 0 0 1rem; margin-left:auto; margin-right:auto;}
div[pressdo-toolbar], h1[pressdo-doc-title]{border: 1px solid #e1e8ed; border-radius: .35rem .35rem 0 0; background-color:#f5f8fa;}
a[pressdo-toolbar-link]{font-size:.9rem; padding:.4rem .8rem;}
a[pressdo-toolbar-link]:not(:last-of-type){border-top-right-radius:0; border-bottom-right-radius:0;}
a[pressdo-toolbar-link]:not(:first-of-type){border-top-left-radius:0; border-bottom-left-radius:0;}
a[pressdo-toolbar-link]:first-of-type{margin-left:0;}
a[pressdo-toolbar-link]{position:relative; float:left; box-shadow:none; border-radius:.35rem; color:#373a3c; background-color:#fff; border-color:#ccc!important;font-weight: 400;line-height: 1.5;text-align: center;white-space: nowrap;cursor: pointer;user-select: none;border: 1px solid transparent;}
div[pressdo-toolbar-menu]{position:relative; display:inline-block; vertical-align:middle;}


*[ion-compass]:before {content: "\f37c";}
*[ion-discuss]:before {content: "\f2d4";}
*[ion-func]:before {content: "\f318";}
*[ion-dropdown]:before { content: "\f35f";}
*[ion-rand]:before {content: "\f4a8";}
*[ion-search]:before {content: "\f2f5";min-width: 15px;display: block;}
*[ion-move]:before {content: "\f133";min-width: 15px;display: block;}
*[ion-unlogined]:before {content: "\f29e";}
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
                        <li pressdo-navitem-nonlist>
                            <a href="/random" pressdo-navitem-nonlist title="무작위">
                                <span ionicon ion-rand></span>
                                <span nav-text>무작위</span>
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
                    </ul>
                    <div pressdo-usermenu>
                <?php if(isset($_SESSION['userid'])){ ?>
                    <a href="#" title="Profile" pressdo-usermenu>
                        <img pressdo-usermenu src=<?=$_SESSION['pfpic']?> alt=<?=$_SESSION['userid']?>>
                    </a><?php
                }else{ ?>
                    <a href="/member/login" title="Login" pressdo-usermenu>
                        <span ionicon ion-unlogined pressdo-usermenu-unlogined></span>
                    </a><?php
                } ?>
                    
                    </div>
                    <form pressdo-search-form>
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
