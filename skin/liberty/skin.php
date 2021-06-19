<?php
namespace PressDo
{
    require 'setting.php';
    class WikiSkin
    {
        public static function Button(array $args, $Doc){
            global $conf, $uri, $lang; ?>
            <div pressdo-toolbar>
                <div pressdo-toolbar-menu><?php
                foreach($args as $arg){
                    ?><a pressdo-sb pressdo-toolbar-link title="<?=$arg?>" href="<?=$uri[strtolower($arg)].$Doc?>"><?=$lang['btn:'.$arg]?></a><?php
                } ?>
                </div>
            </div><?php
        }
        public static function AboveContent(){
            global $conf, $uri, $_liberty, $lang, $_SESSION;
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
                <script async src="/src/script/main.js"></script>
            </head>
            <body>
                    <div pressdo-cover>
                        <div nav-cover>
                            <nav>
                                <a href="/" pressdo-logo><?=$conf['TitleText']?></a>
                                <ul nav-container>
                                    <li pressdo-navitem-nonlist>
                                        <a href="<?=$uri['RecentChanges']?>" pressdo-navitem-nonlist title="<?=$lang['RecentChanges']?>">
                                            <span fa fa-refresh></span>
                                            <span nav-text><?=$lang['menu:RecentChanges']?></span>
                                        </a>
                                    </li>
                                    <li pressdo-navitem-nonlist>
                                        <a href="<?=$uri['RecentDiscuss']?>" pressdo-navitem-nonlist title="<?=$lang['RecentDiscuss']?>">
                                            <span fa fa-comments></span>
                                            <span nav-text><?=$lang['menu:RecentDiscuss']?></span>
                                        </a>
                                    </li>
                                    <li pressdo-navitem-nonlist>
                                        <a href="<?=$uri['random']?>" pressdo-navitem-nonlist title="<?=$lang['random']?>">
                                            <span fa fa-random></span>
                                            <span nav-text><?=$lang['menu:random']?></span>
                                        </a>
                                    </li>
                                    <li pressdo-navitem-listdown>
                                        <a id="nav-menu" onclick="hiddencontents('content-nav-menu')" pressdo-toc-fold=hide pressdo-navitem-listdown title="<?=$lang['SpecialFunctions']?>"><span fa fa-gear></span><span nav-text> <?=$lang['menu:tools']?></span></a>
                                        <div pressdo-navfunc pressdo-toc-fold=hide id="content-nav-menu" role="menu">
                                            <a href="//board.namu.wiki/" title="게시판">
                                                <span class="i ion-ios-clipboard"></span> 
                                                <span class="t">게시판</span>
                                            </a> 
                                            <div pressdo-crossline></div> 
                                            <a href="<?=$uri['NeededPages']?>" title="작성이 필요한 문서">
                                                <span class="i ion-md-alert"></span> 
                                                <span class="t">작성이 필요한 문서</span>
                                            </a><?php foreach ($_SESSION['menus'] as $m) { ?>
                                            <a href="<?=$m['l']?>" title="<?=$m['t']?>">
                                                <span class="i <?=$m['i']?>"></span> 
                                                <span class="t"><?=$m['t']?></span>
                                            </a><?php } ?>
                                        </div>
                                    </li>
                                </ul>
                                <div pressdo-usermenu>
                            <?php if(isset($_SESSION['member']['username'])){ ?>
                                <div pressdo-usermenu-profile>
                                    <a href="#" title="Profile" pressdo-usermenu>
                                        <img pressdo-usermenu src=<?=$_SESSION['member']['gravatar_url']?> alt=<?=$_SESSION['member']['username']?>>
                                    </a>
                                </div>
                                <a href="<?=$uri['logout_r'].base64_encode($_SERVER['REQUEST_URI'])?>" title="Logout" pressdo-usermenu>
                                    <span fa fa-sign-out></span>
                                </a><?php
                            } else { ?>
                                <a href="<?=$uri['login_r'].base64_encode($_SERVER['REQUEST_URI'])?>" title="Login" pressdo-usermenu>
                                    <span fa fa-sign-in></span>
                                </a>
                            <?php } ?>
                                </div>
                                <form pressdo-search-form>
                                    <div pressdo-search-form>
                                        <input type="search" id="search-keyword" name="keyword" placeholder="Search" tabindex="1" pressdo-search autocomplete="off">
                                        <span pressdo-sb>
                                            <button type="button" onclick="sb('<?=$uri['wiki']?>');" pressdo-sb>
                                                <span fa fa-search></span>
                                            </button>
                                            <button type="button" onclick="sb('<?=$uri['search']?>');" pressdo-sb>
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
            global $conf, $uri, $lang;
            WikiSkin::AboveContent(); ?>
            <head>
                <title> 오류 - <?=$conf['SiteName']?> </title>
            </head>
            <div pressdo-content-header>
                <h1 pressdo-doc-title>오류</h1>
            </div>
            <div id="cont_ent" pressdo-doc-content>
                <div><?=$msg?></div>
            </div>
            <footer pressdo-con-footer>
                <?php WikiSkin::BelowContent();
        }
        public static function wiki($Doc, $NS, $Title, $content, $savetime, $rev=null, $raw=false, $err=null){
            global $conf, $uri, $lang;
            WikiSkin::AboveContent();
            if($raw === true)
                $n = 'raw';
            elseif($rev !== null)
                $n = 'rev';
           ?>
                <head>
                    <title> <?=$_GET['title']?> - <?=$conf['SiteName']?> </title>
                </head>
                <div pressdo-content-header>
                    <?=self::Button(['backlink', 'discuss', 'edit', 'history', 'ACL'], $Doc)?>
                    <h1 pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <?php if($rev !== null){ ?><small pressdo-doc-action>(<?=str_replace('@1@',$rev, $lang[$n])?>)</small><?php } ?> 
                    </h1>
                </div>
                <div id="cont_ent" pressdo-doc-content>
                <?php if($err !== null){
                    ?><h2><?=$err?></h2><?php
                      }elseif(!$content && $err == null){ ?>
                    <span><?=$lang['msg:document_not_found']?></span><br>
                    <p><a href="<?=$uri['edit'].$Doc?>">[<?=$lang['create_document']?>]</a></p>
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
                        <li pressdo-doc-changed><?=str_replace('@1@', '<time datetime="'.date("Y-m-d\TH:i:s", $savetime-32400).'.000Z'.'">'.date("Y-m-d H:i:s", $savetime).'</time>', $lang['lastchanged'])?></li>
                        <li pressdo-doc-copyright> <?=stripslashes($conf['CopyRightText'])?></li>
                    </ul>
            <?php WikiSkin::BelowContent();
            
        }
        public static function edit($Doc, $NS, $Title, $raw, $token, $ver=false, $preview=false, $err=false){
            global $conf, $uri, $lang;
            WikiSkin::AboveContent();?>
                <head>
                    <title> <?=$_GET['title']?> (<?=$lang['edit']?>) - <?=$conf['SiteName']?> </title>
                </head>
                <div pressdo-content-header>
                    <?=self::Button(['backlink', 'delete', 'move'],$Doc)?>
                    <h1 pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span pressdo-title-namespace><?=$NS?></span><?=$Title?></a> 
                        <small pressdo-doc-action><?php echo (!$ver)? '('.$lang['editor:create'].')':'('.str_replace('@1@', $ver, $lang['editor:modify']).')'; ?></small>
                    </h1>
                </div>
                <?php //echo (!$raw)? "<p> 새 문서를 생성합니다. </p>": "<p> 문서를 편집하는 중입니다. </p>";?>
                <div pressdo-doc-content pressdo-editor-wrap>
                    <?php if($err){ ?>
                    <div pressdo-alert-box class="a e">
                        <strong pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                        <span pressdo-errorbox-innertext><?=$err?></span>
                    </div>
                    <?php } ?>
                    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
                        <input type="hidden" name="token" value="<?=$token?>">
                        <textarea pressdo-editor id="pressdo-anchor" name="content"><?=$raw?></textarea>
                        <div pressdo-editor-comment>
                            <label pressdo-editor-comment for="logInput"><?=$lang['editor:summary']?></label>
                            <input pressdo-edit-summary type="text" name="summary" id="logInput">
                        </div>
                        <label><input type="checkbox" name="agree" id="agree"> <span><?=stripslashes($conf['EditAgreeText'])?></span></label><?php
                        if (!$_SESSION['member']['username']) { ?>
                            <p pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_edit'])?></p><?php
                        } ?>
                        <div pressdo-buttonarea>
                            <button pressdo-button-editor pressdo-button type="button" onclick="editorMenu('preview', '<?=$uri['edit'].rawurlencode($Doc)?>');"><?=$lang['preview']?></button>
                            <button pressdo-button-editor pressdo-button-blue pressdo-button style="margin-top:0; margin-left:5px;" type="button" onclick="editorMenu('save', '<?=$uri['edit'].rawurlencode($Doc)?>', '<?=$lang['msg:please_agree']?>');">저장</button>
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
            global $conf, $uri, $lang;
            if($conf['UseShortURI'] === true)
                $URIPrefix = '?';
            else
                $URIPrefix = '&';
            WikiSkin::AboveContent();?>
                <head>
                    <title> <?=$_GET['title']?> (<?=$lang['history']?>) - <?=$conf['SiteName']?> </title>
                </head>
                <div pressdo-content-header>
                    <?=self::Button(['edit','backlink'],$Doc)?>
                    <h1 pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <small pressdo-doc-action>(<?=$lang['document_history']?>)</small>
                    </h1>
                </div>
                <div pressdo-doc-content>
                    <form action="<?=$uri['diff'].$Doc?>">
                        <?php  $p_ = (!$prev)?'pressdo-history-null' : 'href='.$uri['history'].$Doc.$URIPrefix.'until='.$prev;
                               $f_ = (!$from)?'pressdo-history-null' : 'href='.$uri['history'].$Doc.$URIPrefix.'from='.$from;?>
                        <p><button pressdo-button type="submit"><?=$lang['compare_revision']?></button></p>
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
                <span pressdo-history-menu>(<a href="<?=$uri['wiki'].$Doc.$URIPrefix.'rev='.$d['rev']?>"><?=$lang['view']?></a> | 
                    <a href="<?=$uri['raw'].$Doc.$URIPrefix.'rev='.$d['rev']?>">RAW</a> | 
                    <a href="<?=$uri['blame'].$Doc.$URIPrefix.'rev='.$d['rev']?>">Blame</a> | 
                    <a href="<?=$uri['revert'].$Doc.$URIPrefix.'rev='.$d['rev']?>"><?=$lang['revert']?></a> | 
                    <a href="<?=$uri['diff'].$Doc.$URIPrefix.'rev='.$d['rev']?>"><?=$lang['diff']?></a><?php
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
                            $italic = '('.str_replace('@1@', $d['target_rev'], $lang['log:revert']).')';
                            break;
                        case 'delete':
                            $italic = '('.$lang['log:delete'].')';
                            break;
                        case 'create':
                            $italic = '('.$lang['log:create'].')';
                            break;
                        case 'move':
                            $italic = '('.str_replace(['@1@', '@2@'], [$d['from'], $d['to']], $lang['log:move']).')';
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
        public static function acl($Doc, $NS, $Title, $docACL, $nsACL, $ACLType, $allow){
            global $lang, $conf, $uri;
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
            foreach ($acl as $a){
                ?><div pressdo-acl-partarea>
                    <h2 pressdo-acl-title><?=$lang[$a.'acl']?></h2><?php
                foreach($ACLType as $A){
                    ?><h4 pressdo-acl-type><?=$lang['acl:'.$A]?></h4>
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
                                        ?><tr pressdo-acl-part><td colspan="5" pressdo-acl-part>(<?=$lang['msg:empty_set_'.$a.'acl']?>)</td></tr><?php
                                    }
                                    $aclcnt = count($acls[$a][$A]);
                                    for($i=0; $i<$aclcnt; ++$i){
                                        $acls[$a][$A][$i]['expired'] = ($acls[$a][$A][$i]['expired'] === '0')? $lang['acl:forever'] : date("Y-m-d H:i:s",$acls[$a][$A][$i]['expired']);
                                        ?><tr pressdo-acl-part><td pressdo-acl-part>
                                            <?=$i+1?></td><td pressdo-acl-part>
                                            <?=$acls[$a][$A][$i]['condition']?></td><td pressdo-acl-part>
                                            <?=$lang['acl:'.$acls[$a][$A][$i]['action']]?></td><td pressdo-acl-part>
                                            <?=$acls[$a][$A][$i]['expired']?></td><td pressdo-acl-part>
                                            <?php if($allow[$a] === true){ ?><button pressdo-button pressdo-button-red type="button" class="d"><?=$lang['acl:delete']?></button></td><?php } ?>
                                        </tr><?php
                                    } ?>
                                </tbody>
                            </table>
                        </div><?php if($allow[$a] === true){ ?>
                        <form pressdo-acl-change method="post">
                            <div pressdo-acl-change class="g">
                                <label pressdo-acl-change>Condition</label> 
                                <div pressdo-acl-change>
                                    <select pressdo-acl-change>
                                        <option pressdo-acl-change value="perm"><?=$lang['perm']?></option> 
                                        <option pressdo-acl-change value="member"><?=$lang['member']?></option> 
                                        <option pressdo-acl-change value="ip"><?=$lang['ip']?></option> 
                                        <option pressdo-acl-change value="geoip"><?=$lang['geoip']?></option> 
                                        <option pressdo-acl-change value="aclgroup"><?=$lang['aclgroup']?></option>
                                    </select> 
                                    <select pressdo-acl-change>
                                        <option pressdo-acl-change value="any"><?=$lang['perm:any']?></option> 
                                        <option pressdo-acl-change value="member"><?=$lang['perm:member']?></option> 
                                        <option pressdo-acl-change value="admin"><?=$lang['perm:admin']?></option> 
                                        <option pressdo-acl-change value="member_signup_15days_ago"><?=$lang['perm:member_singup_15days_ago']?></option> 
                                        <option pressdo-acl-change value="document_contributor"><?=$lang['perm:document_contributor']?></option> 
                                        <option pressdo-acl-change value="contributor"><?=$lang['perm:contributor']?></option> 
                                        <option pressdo-acl-change value="match_username_and_document_title"><?=$lang['perm:match_username_and_document_title']?></option>
                                    </select>
                                </div>
                            </div> 
                            <div pressdo-acl-change class="g">
                                <label pressdo-acl-change>Action :</label> 
                                <div pressdo-acl-change>
                                    <select pressdo-acl-change>
                                        <option pressdo-acl-change value="allow"><?=$lang['acl:allow']?></option> 
                                        <option pressdo-acl-change value="deny"><?=$lang['acl:deny']?></option> 
                                        <option pressdo-acl-change value="gotons"><?=$lang['acl:gotons']?></option>
                                    </select>
                                </div>
                            </div> 
                            <div pressdo-acl-change class="g">
                                <label pressdo-acl-change>Duration :</label> 
                                <div pressdo-acl-change>
                                    <span pressdo-acl-span pressdo-acl-change>
                                        <input pressdo-acl-span type="hidden" name="0" value="0"> 
                                        <select pressdo-acl-span> 
                                            <option pressdo-acl-span value="0"><?=$lang['acl:forever']?></option>
                                            <option pressdo-acl-span value="300"><?=$lang['acl:300s']?></option>
                                            <option pressdo-acl-span value="600"><?=$lang['acl:600s']?></option>
                                            <option pressdo-acl-span value="1800"><?=$lang['acl:1800s']?></option>
                                            <option pressdo-acl-span value="3600"><?=$lang['acl:3600s']?></option>
                                            <option pressdo-acl-span value="7200"><?=$lang['acl:7200s']?></option>
                                            <option pressdo-acl-span value="86400"><?=$lang['acl:86400s']?></option>
                                            <option pressdo-acl-span value="259200"><?=$lang['acl:259200s']?></option>
                                            <option pressdo-acl-span value="432000"><?=$lang['acl:432000s']?></option>
                                            <option pressdo-acl-span value="604800"><?=$lang['acl:604800s']?></option>
                                            <option pressdo-acl-span value="1209600"><?=$lang['acl:1209600s']?></option>
                                            <option pressdo-acl-span value="1814400"><?=$lang['acl:1814400s']?></option>
                                            <option pressdo-acl-span value="2419200"><?=$lang['acl:2419200s']?></option>
                                            <option pressdo-acl-span value="4838400"><?=$lang['acl:4838400s']?></option>
                                            <option pressdo-acl-span value="7257600"><?=$lang['acl:7257600s']?></option>
                                            <option pressdo-acl-span value="14515200"><?=$lang['acl:14515200s']?></option>
                                            <option pressdo-acl-span value="29030400"><?=$lang['acl:29030400s']?></option> 
                                            <option pressdo-acl-span value="raw"><?=$lang['acl:raw']?></option>
                                        </select> 
                                    </span>
                                </div>
                            </div> 
                        <button pressdo-button pressdo-button-blue type="submit" class="s"><?=$lang['acl:add']?></button>
                    </form><?php }
                    } 
                    ?></div><?php
                }?>
                </div>
                <footer pressdo-con-footer><?php WikiSkin::BelowContent();
        }
        public static function login($redirect){
            global $conf, $uri, $lang;
            WikiSkin::AboveContent(); ?>
            <head>
                <title><?=$lang['login']?> - <?=$conf['SiteName']?></title>
            </head>
            <div pressdo-content-header>
                <h1 pressdo-doc-title><?=$lang['login']?></h1>
            </div>
            <div pressdo-doc-content>
                <form pressdo-register id="loginform" name="loginform" method="POST" action="<?=$uri['login_r'].$redirect?>">
                    <div pressdo-loginform class="c">
                        <label pressdo-loginform for="username">Username</label>
                        <input pressdo-formdata type="text" id="username" name="username">
                        <p id="erruser" class="errmsg" style="display:none;"><?=$lang['msg:err_username_required']?></p><p id="erruser" class="errmsg" style="display:none;"><?=$lang['msg:err_wrong_username']?></p>
                    </div>
                    <div pressdo-loginform class="c">
                        <label pressdo-loginform for="password">Password</label>
                        <input pressdo-formdata type="password" id="password" name="password">
                        <p id="errpwd" class="errmsg" style="display:none;"><?=$lang['msg:err_password_required']?></p><p id="errpwd" class="errmsg" style="display:none;"><?=$lang['msg:err_wrong_password']?></p>
                    </div>
                    <div pressdo-loginform class="b">
                        <label pressdo-loginform>
                            <input pressdo-formdata type="checkbox" name="autologin">
                            <span pressdo-formdata><?=$lang['autologin']?></span>
                        </label>
                    </div>
                    <a pressdo-loginform class="a" href="<?=$uri['recover_password']?>">[<?=$lang['recover_password']?>]</a>
                    <div pressdo-buttonarea>
                        <a pressdo-button href="<?=$uri['signup']?>"><?=$lang['signup']?></a>
                        <button type="button" pressdo-button-blue pressdo-button onclick="login()"><?=$lang['login']?></button>
                    </div>
                </form>
            </div>
            <footer pressdo-con-footer>
            <?php 
            WikiSkin::BelowContent();
        }
        public static function signup($mode=0,$err=null){
            global $conf, $uri, $lang;
            WikiSkin::AboveContent(); ?>
            <head>
                <title><?=$lang['signup']?> - <?=$conf['SiteName']?></title>
            </head>
            <div pressdo-content-header>
                <h1 pressdo-doc-title><?=$lang['signup']?></h1>
            </div>
            <div pressdo-doc-content>
                <?php if($err){ ?>
                    <div pressdo-alert-box class="a e">
                        <strong pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                        <span pressdo-errorbox-innertext><?=$err?></span>
                    </div>
                <?php } 
            if($mode === 3){
                unset($_SESSION['MAIL_CHECK'])
                ?><p><?=str_replace('@1@', $_POST['username'], $lang['msg:welcome'])?></p><?php
            }elseif($mode === 2){ ?>
                <form pressdo-register id="signupform" name="signupform" method="POST" action="<?=$uri['signup']?>">
                    <div pressdo-loginform class="c">
                        <label pressdo-loginform for="username"><?=$lang['username']?></label>
                        <input pressdo-formdata type="text" id="username" name="username">
                        <p id="erruser1" class="errmsg" style="display:none;"><?=$lang['msg:err_username_required']?></p><p id="erruser2" class="errmsg" style="display:none;"><?=$lang['msg:err_username_exists']?></p><p id="erruser3" class="errmsg" style="display:none;"><?=$lang['msg:err_username_format']?></p>
                    </div>
                    <div pressdo-loginform class="c">
                        <label pressdo-loginform for="password"><?=$lang['password']?></label>
                        <input pressdo-formdata type="password" id="password" name="password">
                        <p id="errpwd" class="errmsg" style="display:none;"><?=$lang['msg:err_password_required']?></p>
                    </div>
                    <div pressdo-loginform class="c">
                        <label pressdo-loginform for="password2"><?=$lang['password2']?></label>
                        <input pressdo-formdata type="password" id="password2" name="password2">
                        <p id="errpwd21" class="errmsg" style="display:none;"><?=$lang['msg:err_password2_required']?></p><p id="errpwd22" class="errmsg" style="display:none;"><?=$lang['msg:err_wrong_password2']?></p>
                    </div>
                    <b><?=$lang['msg:cannot_delete_account']?></b>
                    <button type="button" pressdo-button-blue pressdo-button-2 pressdo-button onclick="signupcheck()"><?=$lang['signup_submit']?></button>
                </form>
                <?php
            }elseif($mode === 1){ ?>
                <p><?=str_replace('@1@', $_POST['email'], $lang['msg:sent_email'])?></p>
                <ul>
                    <li><?=$lang['msg:check_spam']?></li>
                    <li><?=$lang['msg:email_expiry']?></li>
                </ul>
                <?php
            }elseif($mode === 0){
                $_SESSION['MAIL_CHECK'] = true;?>
                <form pressdo-register method="POST" id="regform" name="regform" action="<?=$uri['signup']?>">
                    <div pressdo-loginform class="c">
                        <label pressdo-loginform for="email"><?=$lang['email']?></label>
                        <input pressdo-formdata type="email" id="email" name="email">
                        <p id="errmail" class="errmsg" style="display:none;"><?=$lang['msg:err_email_required']?></p>
                        <?php
                        if($conf['UseMailWhitelist'] === true){
                            ?><p><?=$lang['msg:use_email_whitelist']?></p>
                            <ul pressdo-mail-whitelist><?php
                            foreach($conf['MailWhitelist'] as $wl){
                                echo "<li pressdo-mail-whitelist>$wl</li>";
                            }
                            echo '</ul>';
                        } ?>
                    </div>
                    <b><?=$lang['msg:cannot_delete_account']?></b>
                    <button type="button" pressdo-button-blue pressdo-button-2 pressdo-button onclick="chkmail()"><?=$lang['signup_submit']?></button>
                    <div style="clear: both;">
                        <div class="grecaptcha-badge" data-style="inline" style="width: 256px; height: 60px; box-shadow: gray 0px 0px 5px;">
                            <div class="grecaptcha-logo">
                                <iframe title="reCAPTCHA" src="https://www.google.com/recaptcha/api2/anchor?ar=1&k=6Le0YCgUAAAAAPuP955bk3npzh_ymfSd53DpI74j&co=aHR0cHM6Ly90aGVzZWVkLmlvOjQ0Mw..&hl=ko&v=6OAif-f8nYV0qSFmq-D6Qssr&size=invisible&badge=inline&cb=8wsokgsumi1k" width="256" height="60" role="presentation" name="a-p1qimm5s15r" frameborder="0" scrolling="no" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation allow-modals allow-popups-to-escape-sandbox">
                                </iframe>
                            </div>
                            <div class="grecaptcha-error">
                            </div>
                            <textarea id="g-recaptcha-response-4" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid rgb(193, 193, 193); margin: 10px 25px; padding: 0px; resize: none; display: none;">
                            </textarea>
                        </div>
                        <iframe style="display: none;">
                        </iframe>
                    </div>
                </form>
                <?php } ?>
            </div>
            <footer pressdo-con-footer>
            <?php 
            WikiSkin::BelowContent();
        }
        public static function admin($type){
            global $conf, $lang, $uri;
            if($type == 'grant'){
            }elseif($type == 'login_history'){
            }
        }
        public static function RecentChanges($h){
            global $conf, $lang, $uri, $_ns;
            WikiSkin::AboveContent();
            ($conf['UseShortURI'] === true)? $d='?':$d='&'; ?>
            <div pressdo-content>
                <div pressdo-content-header>
                    <h1 pressdo-doc-title><?=$lang['RecentChanges']?></h1>
                </div>
                <div pressdo-doc-content>
                    <div>
                        <ol pressdo-recentmenu>
                            <li pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=all">[전체]</a></li>
                            <li pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=create">[새 문서]</a></li>
                            <li pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=delete">[삭제]</a></li>
                            <li pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=move">[이동]</a></li>
                            <li pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=revert">[되돌림]</a></li>
                        </ol>
                        <table pressdo-recent-changes>
                            <colgroup>
                                <col>
                                <col style="width: 25%;">
                                <col style="width: 22%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th pressdo-recent-changes>항목</th>
                                    <th pressdo-recent-changes>수정자</th>
                                    <th pressdo-recent-changes>수정 시간</th>
                                </tr>
                            </thead>
                            <tbody><?php
                                foreach($h as $he) {
                                    ($he['document']['namespace'] == $_ns['document'] && $conf['ForceShowNameSpace'] === false)? $ns = '':$ns = $he['document']['namespace'].':';
                                    $title = $ns.urldecode($he['document']['title']);
                                    $Diff = $he['count'];

                                    // 바이트 수 차이 색깔 표시
                                    if ($Diff > 0)
                                        $Diff = '(<span pressdo-history-green>+'.$Diff.'</span>)';
                                    elseif ($Diff == 0)
                                        $Diff = '(<span pressdo-history-gray>0</span>)';
                                    elseif ($Diff < 0)
                                        $Diff = '(<span pressdo-history-red>'.$Diff.'</span>)';
                                    ?>
                                <tr pressdo-recent-changes class="n">
                                    <td pressdo-recent-changes>
                                        <a href="<?=$uri['wiki'].$title?>"><?=$title?></a> 
                                        <a href="<?=$uri['history'].$title?>">[역사]</a>
                                        <?php if($he['logtype'] !== 'create'){ ?><a href="<?=$uri['diff'].$title.'?rev='.$he['rev']?>">[비교]</a><?php } ?>
                                        <a href="<?=$uri['discuss'].$title?>">[토론]</a>
                                        <span><?=$Diff?></span>
                                    </td>
                                    <td pressdo-recent-changes><div pressdo-popoever><?php 
                                    if($he['author'] === null){
                                        ?><a pressdo-recent-user style="<?=$he['style']?>" pressdo-user-ip><?=$he['ip']?></a><?php
                                    }else{
                                        ?><a pressdo-recent-user style="<?=$he['style']?>" pressdo-user-member><?=$he['author']?></a></div>
                                    <?php } ?></div></td>
                                    <td pressdo-recent-changes><time datetime="<?=date('Y-m-d\TH:i:s', $he['date']-32400)?>"><?=date('Y-m-d H:i:s', $he['date'])?></time></td>
                                </tr><?php 
                                    switch($he['logtype']){
                                        case 'modify':
                                            $italic = '';
                                            break;
                                        case 'revert':
                                            $italic = '('.str_replace('@1@', $he['target_rev'], $lang['log:revert']).')';
                                            break;
                                        case 'delete':
                                            $italic = '('.$lang['log:delete'].')';
                                            break;
                                        case 'create':
                                            $italic = '('.$lang['log:create'].')';
                                            break;
                                        case 'move':
                                            $italic = '('.str_replace(['@1@', '@2@'], [$he['from'], $he['to']], $lang['log:move']).')';
                                            break;
                                    }
                                    if (strlen($he['log']) > 0 || $he['logtype'] !== 'modify') {?>
                                <tr pressdo-recent-changes>
                                    <td pressdo-recent-changes colspan="3">
                                        <span pressdo-recent-changes><?=$he['log']?></span>
                                        <i><?=$italic?></i>
                                    </td>
                                </tr><?php
                                } 
                            }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <footer pressdo-con-footer><?php 
            WikiSkin::BelowContent();
        }
        public static function aclgroup($api){
            global $conf, $lang, $uri;
            $current = $api['currentgroup'];
            $groups = $api['aclgroups'];
            WikiSkin::AboveContent();
            ($conf['UseShortURI'] === true)? $d='?':$d='&'; 
            $p_ = (!$api['until'])?'pressdo-history-null' : 'href='.$uri['history'].$Doc.$URIPrefix.'until='.$api['until'];
            $f_ = (!$api['from'])?'pressdo-history-null' : 'href='.$uri['history'].$Doc.$URIPrefix.'from='.$api['from'];?>
            <head><title>ACLGroup - <?=$conf['SiteName']?></title></head>
            <div pressdo-content-header>
                <h1 pressdo-doc-title>ACLGroup</h1>
            </div>
            <div pressdo-doc-content>
                <ul pressdo-aclgroups><?php
                    foreach ($groups as $g){ 
                        (urldecode($current) == urldecode($g))? $cls = 'a':$cls = '';?>
                    <li pressdo-aclgroup>
                        <a pressdo-aclgroup-link class="<?=$cls?>" href="<?=$uri['aclgroup'].$d.'group='.$g?>"><?=urldecode($g)?><button pressdo-aclgroup-link type="button">×</button></a>
                    </li><?php } ?>
                </ul>
                <form pressdo-aclgroup method="POST" action="<?=$uri['aclgroup']?>" class="a">
                    <input pressdo-aclgroup pressdo-acl-span type="hidden" name="group" value="<?=$g_init?>">
                    <div pressdo-aclgroup class="g">
                        <select pressdo-aclgroup pressdo-acl-span name="mode">
                            <option value="ip"><?=$lang['ip']?></option>
                            <option value="username"><?=$lang['username']?></option>
                        </select>
                        <!--SELECT 결과마다 INPUT NAME,PLACEHOLDER 바꾸기-->
                        <input pressdo-acl-span pressdo-aclgroup type="text" name="ip" placeholder="CIDR">
                    </div>
                    <div pressdo-aclgroup class="g">
                        <label pressdo-aclgroup for="noteInput"><?=$lang['memo']?> :</label>
                        <input pressdo-acl-span pressdo-aclgroup type="text" id="noteInput" name="note">
                    </div>
                    <div pressdo-aclgroup class="g">
                        <label pressdo-aclgroup><?=$lang['expire']?> :</label>
                        <span>
                            <input pressdo-acl-span pressdo-aclgroup type="hidden" name="expire" value="0">
                            <select pressdo-acl-span>
                                <option pressdo-acl-span value="0"><?=$lang['acl:forever']?></option>
                                <option pressdo-acl-span value="300"><?=$lang['acl:300s']?></option>
                                <option pressdo-acl-span value="600"><?=$lang['acl:600s']?></option>
                                <option pressdo-acl-span value="1800"><?=$lang['acl:1800s']?></option>
                                <option pressdo-acl-span value="3600"><?=$lang['acl:3600s']?></option>
                                <option pressdo-acl-span value="7200"><?=$lang['acl:7200s']?></option>
                                <option pressdo-acl-span value="86400"><?=$lang['acl:86400s']?></option>
                                <option pressdo-acl-span value="259200"><?=$lang['acl:259200s']?></option>
                                <option pressdo-acl-span value="432000"><?=$lang['acl:432000s']?></option>
                                <option pressdo-acl-span value="604800"><?=$lang['acl:604800s']?></option>
                                <option pressdo-acl-span value="1209600"><?=$lang['acl:1209600s']?></option>
                                <option pressdo-acl-span value="1814400"><?=$lang['acl:1814400s']?></option>
                                <option pressdo-acl-span value="2419200"><?=$lang['acl:2419200s']?></option>
                                <option pressdo-acl-span value="4838400"><?=$lang['acl:4838400s']?></option>
                                <option pressdo-acl-span value="7257600"><?=$lang['acl:7257600s']?></option>
                                <option pressdo-acl-span value="14515200"><?=$lang['acl:14515200s']?></option>
                                <option pressdo-acl-span value="29030400"><?=$lang['acl:29030400s']?></option> 
                                <option pressdo-acl-span value="raw"><?=$lang['acl:raw']?></option>
                            </select>
                        </span>
                    </div>
                    <button pressdo-button pressdo-button-blue type="submit"><?=$lang['acl:add']?></button>
                </form>
                <div pressdo-toolbar-menu>
                    <a <?=$p_?> pressdo-history-mv>
                        <span ionicon ion-arrow-back></span> Prev
                    </a>
                    <a <?=$f_?> pressdo-history-mv>
                        Next <span ionicon ion-arrow-forward></span>
                    </a>
                </div>
                <form class="s" pressdo-aclgroup method="GET">
                    <div pressdo-aclgroup class="g">
                        <input pressdo-acl-span type="text" name="from" placeholder="ID">
                        <button pressdo-button pressdo-button-blue type="submit">Go</button>
                    </div>
                </form>
                <table pressdo-aclgroup>
                    <colgroup>
                        <col style="width:150px;">
                        <col style="width:150px;">
                        <col>
                        <col style="width:200px;">
                        <col style="width:160px;">
                        <col style="width:60px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th pressdo-recent-changes pressdo-aclgroup>ID</th>
                            <th pressdo-recent-changes pressdo-aclgroup><?=$lang['acl:target']?></th>
                            <th pressdo-recent-changes pressdo-aclgroup><?=$lang['memo']?></th>
                            <th pressdo-recent-changes pressdo-aclgroup><?=$lang['aclgroup:added']?></th>
                            <th pressdo-recent-changes pressdo-aclgroup><?=$lang['aclgroup:expired']?></th>
                            <th pressdo-recent-changes pressdo-aclgroup><?=$lang['aclgroup:action']?></th>
                        </tr>
                    </thead>
                    <tbody><?php 
                        if(count($api['groupmembers']) === 0){
                            ?><td pressdo-aclgroup class="s" colspan="5"><?=$lang['msg:empty_aclgroup']?></td><?php 
                        }else{
                            foreach($api['groupmembers'] as $li){
                                ($li['expiry'] == 0)? $e = $lang['acl:forever']:$e = $li['expiry'];
                                ?><tr>
                                    <td pressdo-aclgroup class="s"><?=$li['id']?></td>
                                    <td pressdo-aclgroup class="s"><?=$li['cidr'].$li['member']?></td>
                                    <td pressdo-aclgroup class="s"><?=$li['memo']?></td>
                                    <td pressdo-aclgroup class="s"><?=date('Y-m-d H:i:s', $li['date'])?></td>
                                    <td pressdo-aclgroup class="s"><?=$e?></td><?php 
                                    if(!($api['accessible'] === true)) $st = 'disabled="disabled"'; ?>
                                    <td pressdo-aclgroup><button <?=$st?> pressdo-button pressdo-button-red type="button"><?=$lang['acl:delete']?></button></td>
                                </tr><?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <footer pressdo-con-footer><?php 
            WikiSkin::BelowContent();
        }
    }
}
