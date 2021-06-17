<?php
namespace PressDo
{
    require 'setting.php';
    class WikiSkin
    {
        public static function AboveContent(){
            global $conf, $uri, $_liberty;
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
                <script async>
                    function hiddencontents(a){
                        b = document.getElementById(a);
                        c = document.getElementById('content-'+a);
                        if(b.getAttribute('pressdo-toc-fold') == 'hide'){
                            b.setAttribute('pressdo-toc-fold', 'show');
                            c.setAttribute('pressdo-toc-fold', 'show');
                        }else{
                        b.setAttribute('pressdo-toc-fold', 'hide');
                            c.setAttribute('pressdo-toc-fold', 'hide');
                        }
                    }
                </script>
            </head>
            <body>
                    <div pressdo-cover>
                        <div nav-cover>
                            <nav>
                                <a href="/" pressdo-logo><?=$conf['TitleText']?></a>
                                <ul nav-container>
                                    <li pressdo-navitem-nonlist>
                                        <a href="<?=$uri['RecentChanges']?>" pressdo-navitem-nonlist title="최근 변경">
                                            <span fa fa-refresh></span>
                                            <span nav-text>최근 변경</span>
                                        </a>
                                    </li>
                                    <li pressdo-navitem-nonlist>
                                        <a href="<?=$uri['RecentDiscuss']?>" pressdo-navitem-nonlist title="최근 토론">
                                            <span fa fa-comments></span>
                                            <span nav-text>최근 토론</span>
                                        </a>
                                    </li>
                                    <li pressdo-navitem-nonlist>
                                        <a href="<?=$uri['random']?>" pressdo-navitem-nonlist title="무작위">
                                            <span fa fa-random></span>
                                            <span nav-text>무작위</span>
                                        </a>
                                    </li>
                                    <li pressdo-navitem-listdown>
                                        <a id="nav-menu" pressdo-toc-fold=hide pressdo-navitem-listdown title="특수 기능"><span fa fa-gear></span><span nav-text>특수 기능</span></a>
                                        <div pressdo-navfunc pressdo-toc-fold=hide id="content-nav-menu" role="menu">
                                            <a href="//board.namu.wiki/" title="게시판" data-v-193fc2b2="" data-v-b986d46e="" data-v-3e5e2b49="">
                                                <span class="i ion-ios-clipboard" data-v-193fc2b2=""></span> 
                                                <span class="t" data-v-193fc2b2="">게시판</span>
                                            </a> 
                                            <div pressdo-crossline></div> 
                                            <a href="<?=$uri['NeededPages']?>" title="작성이 필요한 문서" data-v-193fc2b2="" data-v-b986d46e="" data-v-3e5e2b49="">
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
                                </a>
                                <a href="<?=$uri['logout_r'].$_SERVER['REQUEST_URI']?>" title="Logout" pressdo-usermenu>
                                    <span fa fa-sign-out></span>
                                </a><?php
                            } else { ?>
                                <a href="<?=$uri['login_r'].$_SERVER['REQUEST_URI']?>" title="Login" pressdo-usermenu>
                                    <span fa fa-sign-in></span>
                                </a>
                            <?php } ?>
                                </div>
                                <form pressdo-search-form>
                                    <div pressdo-search-form>
                                        <input type="search" name="keyword" placeholder="Search" tabindex="1" pressdo-search autocomplete="off">
                                        <span pressdo-sb>
                                            <button type="button" onclick="search();" pressdo-sb>
                                                <span fa fa-search></span>
                                            </button>
                                            <button type="button" onclick="godirectly();" pressdo-sb>
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
                                편집 내역
                            </div>
                        </aside>
                        <div pressdo-content> <?php
        }

        public static function BelowContent(){
            global $conf; ?>
                        <ul pressdo-footer-places></ul>
                        <ul pressdo-footer-icons>
                            <li pressdo-footer-poweredby>
                                <a href="//gitlab.com/librewiki/Liberty-MW-Skin">Liberty</a> | 
                                <a href="//github.com/PressDo/PressDoWiki/">PressDo</a>
                            </li>
                        </ul>
                        <?=stripslashes($conf['PageFooter'])?>
                    </footer>
                    </div></body><?php
        }
        public static function error($msg) {
        }

        public static function wiki($Doc, $NS, $Title, $content, $savetime, $rev=null, $raw=false, $err=null){
            global $conf, $uri;
            WikiSkin::AboveContent();
            if($raw === true)
                $sfx = 'RAW';
            elseif($rev !== null)
                $sfx = '판';
           ?>
                <head>
                    <title> <?=$_GET['title']?> - <?=$conf['SiteName']?> </title>
                </head>
                <div pressdo-content-header>
                    <div pressdo-toolbar>
                        <div pressdo-toolbar-menu>
                            <a pressdo-toolbar-link href="<?=$uri['backlink'].$Doc?>">역링크</a>
                            <a pressdo-toolbar-link href="<?=$uri['discuss'].$Doc?>">토론</a>
                            <a pressdo-toolbar-link href="<?=$uri['edit'].$Doc?>">편집</a>
                            <a pressdo-toolbar-link href="<?=$uri['history'].$Doc?>">역사</a>
                            <a pressdo-toolbar-link pressdo-toolbar-last href="<?=$uri['acl'].$Doc?>">ACL</a>
                        </div>
                    </div>
                    <h1 pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <?php if($rev !== null){ ?><small pressdo-doc-action>(r<?=$rev?> 판)</small><?php } ?> 
                    </h1>
                </div>
                <div id="cont_ent" pressdo-doc-content>
                <?php if($err !== null){
                    ?><h2><?=$err?></h2><?php
                      }elseif(!$content && $err == null){ ?>
                    <span> 해당 문서를 찾을 수 없습니다. </span><br>
                    <p><a href="<?=$uri['edit'].$Doc?>">[새 문서 만들기]</a></p>
                <?php }elseif($raw === true){
                ?><textarea readonly="readonly" pressdo-editor><?=$content?></textarea><?php
                      }else{ ?>
                    <div id="categoryspace_top"></div>
                    <?=$content?>
                    <script async>
                        // 분류 위치 조정
                        var f = document.getElementById('categories');
                        var parent = f.parentNode.parentNode;
                        parent.insertBefore(f, parent.childNodes[0]);
                    </script>
                <?php } ?>
                    </div>
                    <footer pressdo-con-footer>
                        <ul>
                            <li pressdo-doc-changed>이 문서는 <time datetime="<?=date("Y-m-d\TH:i:s", $savetime-32400).'.000Z'?>"><?=date("Y-m-d H:i:s", $savetime)?></time> 에 마지막으로 바뀌었습니다.</li>
                            <li pressdo-doc-copyright> <?=stripslashes($conf['CopyRightText'])?></li>
                        </ul>
            <?php WikiSkin::BelowContent();
            
        }
        public static function edit($Doc, $NS, $Title, $raw, $token, $ver=false, $preview=false, $err=false){
            global $conf, $uri;
            WikiSkin::AboveContent();?>
                <head>
                    <title> <?=$_GET['title']?> (편집) - <?=$conf['SiteName']?> </title>
                </head>
                <div pressdo-content-header>
                    <div pressdo-toolbar>
                        <div pressdo-toolbar-menu>
                            <a pressdo-toolbar-link href="<?=$uri['backlink'].$Doc?>">역링크</a>
                            <a pressdo-toolbar-link pressdo-toolbar-d rel="nofollow" href="<?=$uri['delete'].$Doc?>">삭제</a>
                            <a pressdo-toolbar-link pressdo-toolbar-last rel="nofollow" href="<?=$uri['move'].$Doc?>">이동</a>
                        </div>
                    </div>
                    <h1 pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span pressdo-title-namespace><?=$NS?></span><?=$Title?></a> 
                        <small pressdo-doc-action><?php echo (!$ver)? '(새 문서 생성)':"(r".$ver ." 편집)"; ?></small>
                    </h1>
                </div>
                <?php //echo (!$raw)? "<p> 새 문서를 생성합니다. </p>": "<p> 문서를 편집하는 중입니다. </p>";
?>              <div pressdo-doc-content pressdo-editor-wrap>
                    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
                        <input type="hidden" name="token" value="<?=$token?>">
                        <textarea pressdo-editor id="pressdo-anchor" name="content"><?=$raw?></textarea>
                        <div pressdo-editor-comment>
                            <label pressdo-editor-comment for="logInput">요약</label>
                            <input pressdo-edit-summary type="text" name="summary" id="logInput">
                        </div>
                        <label><input type="checkbox" name="agree" id="agree"> <span><?=stripslashes($conf['EditAgreeText'])?></span></label><?php
                        if (!$_SESSION['userid']) { ?>
                            <p pressdo-warning-unlogined>비로그인 상태로 편집합니다. 편집 역사에 IP(<?=PressDo::getip()?>)가 영구히 기록됩니다.</p><?php
                        } ?>
                        <div pressdo-buttonarea>
                            <button pressdo-button-editor pressdo-button type="button" onclick="editorMenu('preview');">미리 보기</button>
                            <button pressdo-button-editor pressdo-button-blue pressdo-button style="margin-top:0; margin-left:5px;" type="button" onclick="editorMenu('save');">저장</button>
                            <script async>
                            // 버튼
                            var f = document.getElementById('editForm');
                            function editorMenu(action) {
                                n = document.getElementById('pressdo-anchor')
                                if(action == 'preview'){
                                    f.action = '<?=$uri['edit'].rawurlencode($Doc)?>';
                                    var parent = n.parentNode;
                                    parent.insertBefore(f, parent.childNodes[6]);
                                }
                                if(action == 'save'){
                                    if(!document.editForm.agree.checked){
                                        alert('수정하기 전에 먼저 문서 배포 규정에 동의해 주세요.');
                                        //var parent = n.parentNode;
                                        //parent.insertBefore(f, parent.childNodes[1]);
                                        return false;
                                    }
                                    f.action = '<?=$uri['edit'].rawurlencode($Doc)?>'; 
                                }
                                f.submit();
                            }
                            </script>
                        </div>
                    </form>
                </div>
            <footer pressdo-con-footer><?php
                /*if(strlen($preview) > 0){
                    ?><p> 아래는 저장되지 않은 미리 보기의 모습입니다. </p><hr><?php
                    echo PressDo::readSyntax($raw);
                }*/?><?php WikiSkin::BelowContent();
        }

        public static function history($Doc, $NS, $Title, $data, $prev, $next){
            global $conf, $uri;
            if($conf['UseShortURI'] === true)
                $URIPrefix = '?';
            else
                $URIPrefix = '&';
            WikiSkin::AboveContent();?>
                <head>
                    <title> <?=$_GET['title']?> (역사) - <?=$conf['SiteName']?> </title>
                </head>
                <div pressdo-content-header>
                    <div pressdo-toolbar>
                        <div pressdo-toolbar-menu>
                            <a pressdo-toolbar-link href="<?=$uri['edit'].$Doc?>">편집</a>
                            <a pressdo-toolbar-link href="<?=$uri['backlink'].$Doc?>">역링크</a>
                        </div>
                    </div>
                    <h1 pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <small pressdo-doc-action>(문서 역사)</small>
                    </h1>
                </div>
                <div pressdo-doc-content>
                    <form action="<?=$uri['diff'].$Doc?>">
                        <?php  $p_ = (!$prev)?'pressdo-history-null' : 'href='.$uri['history'].$Doc.$URIPrefix.'until='.$prev;
                               $f_ = (!$from)?'pressdo-history-null' : 'href='.$uri['history'].$Doc.$URIPrefix.'from='.$from;?>
                        <p><button pressdo-button type="submit">선택 리비전 비교</button></p>
                        <div pressdo-toolbar-menu>
                            <a <?=$p_?> pressdo-history-mv>
                                <span ionicon ion-arrow-back></span> Prev
                            </a>
                            <a <?=$f_?> pressdo-history-mv>
                                Next <span ionicon ion-arrow-forward></span>
                            </a>
                        </div>
                        <ul pressdo-history><?php
            foreach($data as $d) {
                // 바이트 수 차이 색깔 표시
                if($d['count'] > 0)
                    $Diff = '(<span pressdo-history-green>+'.$d['count'].'</span>)';
                elseif($d['count'] === 0)
                    $Diff = '(<span pressdo-history-gray>0</span>)';
                elseif($d['count'] < 0)
                    $Diff = '(<span pressdo-history-red>'.$d['count'].'</span>)';
                ?><li pressdo-history><time datetime="<?=date('Y-m-d\TH:i:s',$d['date']-32400).'.000Z'?>"><?=date('Y-m-d H:i:s',$d['date'])?></time> 
                <span pressdo-history-menu>(<a href="<?=$uri['wiki'].$Doc.$URIPrefix.'rev='.$d['rev']?>">보기</a> | 
                    <a href="<?=$uri['raw'].$Doc.$URIPrefix.'rev='.$d['rev']?>">RAW</a> | 
                    <a href="<?=$uri['blame'].$Doc.$URIPrefix.'rev='.$d['rev']?>">Blame</a> | 
                    <a href="<?=$uri['revert'].$Doc.$URIPrefix.'rev='.$d['rev']?>">이 리비전으로 되돌리기</a> | 
                    <a href="<?=$uri['diff'].$Doc.$URIPrefix.'rev='.$d['rev']?>">비교</a><?php
            /*if(Data::inACLgroup($user['typename'], 'admin')){
            ?> | 
                    <a href="<?=$uri['hide'].$Doc.'?rev='.$d['rev']?>">숨기기</a><?php
            }*/
            ?>)
                </span>
                    <input type="radio" name="oldrev" value="<?=$d['rev']?>" style="visibility: visible;"><input type="radio" name="rev" value="<?=$d['rev']?>" style="visibility: visible;">
                    <?php
                    switch($d['logtype']){
                        case 'modify':
                            $italic = '';
                            break;
                        case 'revert':
                            $italic = '(r'.$d['target_rev'].'으로 되돌림)';
                            break;
                        case 'delete':
                            $italic = '(삭제)';
                            break;
                        case 'create':
                            $italic = '(새 문서)';
                            break;
                        case 'move':
                            $italic = '('.$d['from'].'에서 '.$d['to'].'으로 문서 이동)';
                            break;
                    }
                            ?><i><?=$italic?></i>
                    <strong>r<?=$d['rev']?></strong> <span><?=$Diff?></span>
                    <div pressdo-history-menu style="display:inline;"><?php
                        if($d['author'] === null){
                            ?><span style="<?=$d['style']?>" pressdo-user-ip><?=$d['ip']?></span><?php
                        }else{
                            ?><span style="<?=$d['style']?>" pressdo-user-member><?=$d['author']?></span></div>
                        <?php } ?>
                    (<span style="color:grey"><?=$d['log']?></span>)</li><?php
            } ?>
                        </ul>
                        <div pressdo-toolbar-menu>
                            <a <?=$p_?> pressdo-history-mv>
                                <span ionicon ion-arrow-back></span> Prev
                            </a>
                            <a <?=$f_?> pressdo-history-mv>
                                Next <span ionicon ion-arrow-forward></span>
                            </a>
                        </div>
                    </form></div>
                <footer pressdo-con-footer><?php WikiSkin::BelowContent();
        }
        
        public static function acl($Doc, $NS, $Title, $docACL, $nsACL, $ACLType)
        {
            global $conf, $uri;
            if($conf['UseShortURI'] === true)
                $URIPrefix = '?';
            else
                $URIPrefix = '&';
            WikiSkin::AboveContent();?><style>
                h4[pressdo-acl-type]{font-size:1.4em;margin:1.2em 0 .8em;font-weight:700;font-family:inherit;line-height:1.1;color:inherit}h2[pressdo-acl-title]{font-size:1.8em;margin:1.2em 0 .8em;font-weight:700;font-family:inherit;line-height:1.1;color:inherit}div[pressdo-acl-part]{overflow-x:auto}div[pressdo-acl-part],table[pressdo-acl-part]{width:100%;max-width:100%}table[pressdo-acl-part]{margin-bottom:1rem;background-color:transparent;border-spacing:0;border-collapse:collapse;white-space:nowrap}table tr:last-of-type td[pressdo-acl-part]{border-bottom:1px solid #eceeef}td[colspan][pressdo-acl-part]{text-align:center;cursor:auto}td[pressdo-acl-part]{cursor:move}td[pressdo-acl-part],th[pressdo-acl-part]{padding:.5rem .7rem;line-height:1.5;border-top:1px solid #eceeef}th[pressdo-acl-part]{vertical-align:bottom;border-bottom:2px solid #eceeef;text-align:left}
              </style><head>
                    <title> <?=$_GET['title']?> (ACL) - <?=$conf['SiteName']?> </title>
                </head>
                <div pressdo-content-header>
                    <h1 pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <small pressdo-doc-action>(ACL)</small>
                    </h1>
                </div>
                <div pressdo-doc-content>
                    <?php
                $acl = ['doc', 'ns'];
                $acls = ['doc' => $docACL, 'ns' => $nsACL];
                $msg = ['doc' => ['문서 ACL', '규칙이 존재하지 않습니다. 이름공간 ACL이 적용됩니다.'], 'ns' => ['이름공간 ACL', '규칙이 존재하지 않습니다. 모두 거부됩니다.']];
            foreach ($acl as $a){
                ?><div pressdo-acl-partarea>
                    <h2 pressdo-acl-title><?=$msg[$a][0]?></h2><?php
                foreach($ACLType as $A){
                    ?><h4 pressdo-acl-type><?=$A?></h4>
                        <div pressdo-acl-part>
                            <table pressdo-acl-part>
                                <colgroup pressdo-acl-part>
                                    <col pressdo-acl-part style="width:60px;">
                                    <col pressdo-acl-part>
                                    <col pressdo-acl-part style="width:80px;">
                                    <col pressdo-acl-part style="width:200px;">
                                    <col pressdo-acl-part style="width:60px;">
                                </colgroup> 
                                <thead pressdo-acl-part>
                                    <tr pressdo-acl-part>
                                        <th pressdo-acl-part>No</th>
                                        <th pressdo-acl-part>Condition</th>
                                        <th pressdo-acl-part>Action</th>
                                        <th pressdo-acl-part>Expiration</th>
                                        <th pressdo-acl-part></th>
                                    </tr>
                                </thead>
                                <tbody pressdo-acl-part><?php
                                    if(!$acls[$a][$A][0]){
                                        ?><tr pressdo-acl-part><td colspan="5" pressdo-acl-part>(<?=$msg[$a][1]?>)</td></tr><?php
                                    }
                                    $aclcnt = count($acls[$a][$A]);
                                    for($i=0; $i<$aclcnt; ++$i){
                                        if($acls[$a][$A][$i]['expired'] === '0') $acls[$a][$A][$i]['expired'] = '영구';
                                        ?><tr pressdo-acl-part><td pressdo-acl-part>
                                            <?=$i+1?></td><td pressdo-acl-part>
                                            <?=$acls[$a][$A][$i]['condition']?></td><td pressdo-acl-part>
                                            <?=$acls[$a][$A][$i]['action']?></td><td pressdo-acl-part>
                                            <?=$acls[$a][$A][$i]['expired']?></td>
                                        </tr><?php
                                    } ?>
                                </tbody>
                            </table>
                        </div><?php
                    } 
                    ?></div><?php
                }?>
                </div>
                <footer pressdo-con-footer><?php WikiSkin::BelowContent();
        }
    }
}
