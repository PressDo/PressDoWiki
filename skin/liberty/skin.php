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
            aside{
                position:relative;
                margin: 1rem 1rem 1rem 0;
                order:1;
                width:17rem;
                display:block;
            }
            article{
                width:60%;
                border-left: 1px solid #ccc;
                border-right: 1px solid #ccc;
                padding:1.5rem;
            }
            article>h1{
                margin: 0 0 2rem;
                font-size:2.2rem;
            }
            section{
                display:flex;
                width:100%;
                margin: 0 auto;
                max-width:1300px;
            }
                        </style>
        <div pressdo-cover>
            <div nav-cover>
                <nav>
                    <a href="/" pressdo-logo></a>
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
                            <div pressdo-navfunc>
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
