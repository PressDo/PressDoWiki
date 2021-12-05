<?php
namespace PressDo
{
    class SkinTemplate
    {
        private static function Button(array $args, $Doc)
        {
            global $conf, $uri, $lang; ?>
            <div data-pressdo-toolbar>
                <div data-pressdo-toolbar-menu><?php
                foreach($args as $arg){
                    ?><a data-pressdo-sb data-pressdo-toolbar-link title="<?=$arg?>" href="<?=$uri[strtolower($arg)].$Doc?>"><?=$lang['btn:'.$arg]?></a><?php
                } ?>
                </div>
            </div><?php
        }
        public static function error($msg) 
        {
            $msg = $msg[0];
            global $conf, $uri, $lang; ?>
            <head>
                <title> 오류 - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title>오류</h1>
            </div>
            <div id="cont_ent" data-pressdo-doc-content>
                <div><?=$msg?></div>
            </div>
            <footer data-pressdo-con-footer><?php
        }
        public static function wiki($args)
        {
            list($Doc, $NS, $Title, $content, $savetime, $rev, $mode, $err) = $args;
            global $conf, $uri, $lang;
            if($mode == 'raw' || $mode == 'diff')
                $n = $mode;
            elseif($rev !== null)
                $n = 'rev';
            if(!$content || $mode == 'raw' || $mode == 'diff' || $mode == 'disabled')
                $hideTime = 'none';
            else
                $hideTime = 'block';
           ?>
                <head>
                    <meta charset="UTF-8">
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.11.1/dist/katex.min.css" integrity="sha384-zB1R0rpPzHqg7Kpt0Aljp8JPLqbXI3bhnPWROx27a9N0Ll6ZP/+DiW/UqRcLbRjq" crossorigin="anonymous"/>
                    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.11.1/dist/katex.min.js" integrity="sha384-y23I5Q6l+B6vatafAwxRu/0oK/79VlbSz7Q9aiSZUvyWYIYsd+qj+o24G5ZU2zJz" crossorigin="anonymous"></script>
                    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.11.1/dist/contrib/auto-render.min.js" integrity="sha384-kWPLUVMOks5AQFrykwIup5lo0m3iMkkHrD0uJ4H5cjeGihAutqP0yW0J6dpFiVkI" crossorigin="anonymous" onload="renderMathInElement(document.body);"></script>
                    <script defer src="/src/script/KaTeX.js"></script>
                    <title> <?=$_GET['title']?> - <?=$conf['SiteName']?> </title>
                </head>
                <div data-pressdo-content-header>
                    <?=self::Button(['backlink', 'discuss', 'edit', 'history', 'ACL'], $Doc)?>
                    <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <?php if($rev !== null){ ?><small data-pressdo-doc-action>(<?=str_replace('@1@',$rev, $lang[$n])?>)</small><?php } ?> 
                    </h1>
                </div>
                <div id="cont_ent" data-pressdo-doc-content>
                    <?php if($err['errbox']){ ?>
                    <div data-pressdo-alert-box class="a e">
                        <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                        <span data-pressdo-errorbox-innertext><?=$err['errbox']?></span>
                    </div>
                    <?php }
                    if($err['error'] !== null){ ?>
                        <h2><?=$err['error']?></h2><?php
                    }elseif($content === null && $err['error'] == null){ 
                        if($mode == 'diff'){ ?>
                        <span><?=$lang['msg:no_revision']?></span><?php
                        }else{?>
                        <span><?=$lang['msg:document_not_found']?></span><br>
                        <p><a href="<?=$uri['edit'].$Doc?>">[<?=$lang['create_document']?>]</a></p><?php 
                        }
                    }elseif($mode == 'raw' || $mode == 'disabled'){ ?>
                        <textarea readonly="readonly" data-pressdo-editor><?=$content?></textarea><?php
                    }else{ ?>
                        <div id="categoryspace_top"></div>
                        <?=$content?><?php 
                    } ?>
                </div>
                <footer data-pressdo-con-footer>
                    <ul>
                        <li data-pressdo-doc-changed v="<?=$hideTime?>"><?=str_replace('@1@', '<time datetime="'.date("Y-m-d\TH:i:s", $savetime-32400).'.000Z'.'">'.date("Y-m-d H:i:s", $savetime).'</time>', $lang['lastchanged'])?></li>
                        <li data-pressdo-doc-copyright> <?=stripslashes($conf['CopyRightText'])?></li>
                    </ul><?php
        }
        public static function edit($args)
        {
            list($Doc, $NS, $Title, $raw, $token, $ver, $preview, $err) = $args;
            global $conf, $uri, $lang;?>
                <head>
                    <title> <?=$_GET['title']?> (<?=$lang['edit']?>) - <?=$conf['SiteName']?> </title>
                </head>
                <div data-pressdo-content-header>
                    <?=self::Button(['backlink', 'delete', 'move'],$Doc)?>
                    <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a> 
                        <small data-pressdo-doc-action><?php echo (!$ver)? '('.$lang['editor:create'].')':'('.str_replace('@1@', $ver, $lang['editor:modify']).')'; ?></small>
                    </h1>
                </div>
                <div data-pressdo-doc-content data-pressdo-editor-wrap>
                    <?php if($err){ ?>
                    <div data-pressdo-alert-box class="a e">
                        <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                        <span data-pressdo-errorbox-innertext><?=$err?></span>
                    </div>
                    <?php } ?>
                    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
                        <input type="hidden" name="token" value="<?=$token?>">
                        <textarea data-pressdo-editor id="pressdo-anchor" name="content"><?=$raw?></textarea>
                        <div data-pressdo-editor-comment>
                            <label data-pressdo-editor-comment for="logInput"><?=$lang['editor:summary']?></label>
                            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
                        </div>
                        <label><input type="checkbox" name="agree" id="agree"> <span><?=stripslashes($conf['EditAgreeText'])?></span></label><?php
                        if (!$_SESSION['member']['username']) { ?>
                            <p data-pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_edit'])?></p><?php
                        } ?>
                        <div data-pressdo-buttonarea>
                            <button data-pressdo-button-editor data-pressdo-button type="button" id="ef" editor-action="preview" editor-uri="<?=$uri['edit'].rawurlencode($Doc)?>"><?=$lang['preview']?></button> 
                            <button data-pressdo-button-editor data-pressdo-button-blue data-pressdo-button data-pressdo-button-editor-submit type="button" id="ef" editor-action="save" editor-uri="<?=$uri['edit'].rawurlencode($Doc)?>" editor-msg="<?=$lang['msg:please_agree']?>">저장</button>
                        </div>
                    </form>
                </div>
            <footer data-pressdo-con-footer><?php
                /*if(strlen($preview) > 0){
                    ?><p> 아래는 저장되지 않은 미리 보기의 모습입니다. </p><hr><?php
                    echo PressDo::readSyntax($raw);
                }*/
        }
        public static function delete($args)
        {
            list($Doc, $NS, $Title) = $args;
            global $conf, $uri, $lang;?>
                <head>
                    <title> <?=$_GET['title']?> (<?=$lang['delete']?>) - <?=$conf['SiteName']?> </title>
                </head>
                <div data-pressdo-content-header>
                    <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a> 
                        <small data-pressdo-doc-action>(<?=$lang['delete']?>)</small>
                    </h1>
                </div>
                <div data-pressdo-doc-content data-pressdo-editor-wrap>
                    <?php if($err){ ?>
                    <div data-pressdo-alert-box class="a e">
                        <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                        <span data-pressdo-errorbox-innertext><?=$err?></span>
                    </div>
                    <?php } ?>
                    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
                        <div data-pressdo-editor-comment data-pressdo-loginform class="c">
                            <label data-pressdo-editor-comment for="logInput"><?=$lang['editor:summary']?></label>
                            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
                        </div>
                        <label data-pressdo-pagelist><input type="checkbox" name="agree" id="agree"> <?=$lang['msg:ThisIsNotMove']?></label>
                        <p data-pressdo-delete><b><?=str_replace('@1@', $uri['move'].$Doc, $lang['msg:PleaseDontMove'])?></b></p><?php
                        if (!$_SESSION['member']['username']) { ?>
                            <p data-pressdo-delete data-pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_edit'])?></p><?php
                        } ?>
                        <div data-pressdo-buttonarea>
                            <button data-pressdo-button-editor data-pressdo-button data-pressdo-button-red editor-msg="<?=$lang['msg:CheckDelMsg']?>" type="button" id="delb"><?=$lang['delete']?></button>
                        </div>
                    </form>
                </div>
            <footer data-pressdo-con-footer><?php
        }
        public static function move($args)
        {
            list($Doc, $NS, $Title,$token,$err) = $args;
            global $conf, $uri, $lang;?>
                <head>
                    <title> <?=$_GET['title']?> (<?=$lang['move']?>) - <?=$conf['SiteName']?> </title>
                </head>
                <div data-pressdo-content-header>
                    <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a> 
                        <small data-pressdo-doc-action>(<?=$lang['move']?>)</small>
                    </h1>
                </div>
                <div data-pressdo-doc-content data-pressdo-editor-wrap>
                    <?php if($err){ ?>
                    <div data-pressdo-alert-box class="a e">
                        <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                        <span data-pressdo-errorbox-innertext><?=$err?></span>
                    </div>
                    <?php } ?>
                    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?=$token?>">
                        <div data-pressdo-editor-comment data-pressdo-loginform class="c">
                            <label data-pressdo-editor-comment for="newTitle"><?=$lang['title_to_move']?></label>
                            <input data-pressdo-edit-summary type="text" name="new_title" id="newTitle">
                        </div>
                        <div data-pressdo-editor-comment data-pressdo-loginform class="c">
                            <label data-pressdo-editor-comment for="logInput"><?=$lang['editor:summary']?></label>
                            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
                        </div>
                        <label data-pressdo-pagelist><input type="checkbox" name="change" value="Y"> <?=$lang['change_document']?></label>
                        <div data-pressdo-buttonarea>
                            <button data-pressdo-button-editor data-pressdo-button data-pressdo-button-blue type="submit"><?=$lang['move']?></button>
                        </div>
                    </form>
                </div>
            <footer data-pressdo-con-footer><?php
        }
        public static function history($args)
        {
            list($Doc, $NS, $Title, $data, $prev, $next) = $args;
            global $conf, $uri, $lang; ?>
                <head>
                    <title> <?=$_GET['title']?> (<?=$lang['history']?>) - <?=$conf['SiteName']?> </title>
                </head>
                <div data-pressdo-content-header>
                    <?=self::Button(['edit','backlink'],$Doc)?>
                    <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <small data-pressdo-doc-action>(<?=$lang['document_history']?>)</small>
                    </h1>
                </div>
                <div data-pressdo-doc-content>
                    <form action="<?=$uri['diff'].$Doc?>" method="GET">
                        <?php  $p_ = (!$prev)?'data-pressdo-history-null' : 'href='.$uri['history'].$Doc.$uri['prefix'].'until='.$prev;
                               $f_ = (!$next)?'data-pressdo-history-null' : 'href='.$uri['history'].$Doc.$uri['prefix'].'from='.$next;?>
                        <p><button data-pressdo-button type="submit"><?=$lang['compare_revision']?></button></p>
                        <div data-pressdo-toolbar-menu>
                            <a <?=$p_?> data-pressdo-history-mv>
                                <span ionicon ion-arrow-back></span> Prev
                            </a>
                            <a <?=$f_?> data-pressdo-history-mv>
                                Next <span ionicon ion-arrow-forward></span>
                            </a>
                        </div>
                        <ul data-pressdo-history><?php
            foreach($data as $d) {
                // 바이트 수 차이 색깔 표시
                if($d['count'] > 0)
                    $Diff = '(<span data-pressdo-history-green>+'.$d['count'].'</span>)';
                elseif($d['count'] === 0)
                    $Diff = '(<span data-pressdo-history-gray>0</span>)';
                elseif($d['count'] < 0)
                    $Diff = '(<span data-pressdo-history-red>'.$d['count'].'</span>)';
                ?><li data-pressdo-history><time datetime="<?=date('Y-m-d\TH:i:s',$d['date']-32400).'.000Z'?>"><?=date('Y-m-d H:i:s',$d['date'])?></time> 
                <span data-pressdo-history-menu>(<a href="<?=$uri['wiki'].$Doc.$uri['prefix'].'rev='.$d['rev']?>"><?=$lang['view']?></a> | 
                    <a href="<?=$uri['raw'].$Doc.$uri['prefix'].'rev='.$d['rev']?>">RAW</a> | 
                    <?php /*<a href="<?=$uri['blame'].$Doc.$uri['prefix'].'rev='.$d['rev']?>">Blame</a> | */?>
                    <a href="<?=$uri['revert'].$Doc.$uri['prefix'].'rev='.$d['rev']?>"><?=$lang['revert']?></a> | 
                    <a href="<?=$uri['diff'].$Doc.$uri['prefix'].'rev='.$d['rev']?>"><?=$lang['diff']?></a><?php 
            if($d['ip'] != '' && $d['ip'] == PressDo::getip() && isset($_SESSION['member']['username'])){
            ?> | 
                    <a href="<?=$uri['move_ip'].$Doc.'?dt='.$d['date']?>">이 기여를 로그인 사용자로 이전하기</a><?php
            }
            /*if(Data::inACLgroup($user['typename'], 'admin')){
            ?> | 
                    <a href="<?=$uri['hide'].$Doc.'?rev='.$d['rev']?>">숨기기</a><?php
            }*/
            ?>)
                </span>
                    <input data-pressdo-history-radio type="radio" name="oldrev" value="<?=$d['rev']?>" v="inline-block"><input data-pressdo-history-radio type="radio" name="rev" value="<?=$d['rev']?>"  v="inline-block">
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
                    <div data-pressdo-history-menu style="display:inline;"><?php
                        if($d['author'] === null){
                            ?><span style="<?=$d['style']?>" data-pressdo-user-ip><?=$d['ip']?></span><?php
                        }else{
                            ?><span style="<?=$d['style']?>" data-pressdo-user-member><?=$d['author']?></span></div>
                        <?php } ?>
                    (<span data-pressdo-history-gray><?=$d['log']?></span>)</li><?php
            } ?>
                        </ul>
                        <div data-pressdo-toolbar-menu>
                            <a <?=$p_?> data-pressdo-history-mv>
                                <span ionicon ion-arrow-back></span> Prev
                            </a>
                            <a <?=$f_?> data-pressdo-history-mv>
                                Next <span ionicon ion-arrow-forward></span>
                            </a>
                        </div>
                    </form></div>
                <footer data-pressdo-con-footer><?php
        }
        public static function acl($args)
        {
            list($err, $Doc, $NS, $Title, $docACL, $nsACL, $ACLType, $allow) = $args;
            global $lang, $conf, $uri; ?>
                <?php if($err !== null) {?><script>alert('<?=$err?>');</script><?php } ?>
                <head>
                    <title> <?=$_GET['title']?> (ACL) - <?=$conf['SiteName']?> </title>
                </head>
                <div data-pressdo-content-header>
                    <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                        <small data-pressdo-doc-action>(ACL)</small>
                    </h1>
                </div>
                <form method="POST" id="rf" action="<?=$_SERVER['REQUEST_URI']?>">
                    <input type="hidden" id="rft" name="target" value>
                </form>
                <div data-pressdo-doc-content>
                    <?php
                $acl = ['doc', 'ns'];
                $acls = ['doc' => $docACL, 'ns' => $nsACL];
            foreach ($acl as $a){
                ?><div data-pressdo-acl-partarea>
                    <h2 data-pressdo-acl-title><?=$lang[$a.'acl']?></h2><?php
                foreach($ACLType as $A){
                    ?><h4 data-pressdo-acl-type><?=$lang['acl:'.$A]?></h4>
                        <div data-pressdo-acl-part>
                            <table data-pressdo-acl-part>
                                <colgroup data-pressdo-acl-part>
                                    <col data-pressdo-acl-part style="width:60px;">
                                    <col data-pressdo-acl-part>
                                    <col data-pressdo-acl-part style="width:80px;">
                                    <col data-pressdo-acl-part style="width:200px;">
                                    <col data-pressdo-acl-part style="width:60px;">
                                </colgroup> 
                                <thead data-pressdo-acl-part>
                                    <tr data-pressdo-acl-part>
                                        <th data-pressdo-acl-part>No</th>
                                        <th data-pressdo-acl-part>Condition</th>
                                        <th data-pressdo-acl-part>Action</th>
                                        <th data-pressdo-acl-part>Expiration</th>
                                        <th data-pressdo-acl-part></th>
                                    </tr>
                                </thead>
                                <tbody data-pressdo-acl-part>
                                    <?php
                                    if(!$acls[$a][$A][0]){
                                        ?><tr data-pressdo-acl-part><td colspan="5" data-pressdo-acl-part>(<?=$lang['msg:empty_set_'.$a.'acl']?>)</td></tr><?php
                                    }
                                    $aclcnt = count($acls[$a][$A]);
                                    for($i=0; $i<$aclcnt; ++$i){
                                        $acls[$a][$A][$i]['expired'] = ($acls[$a][$A][$i]['expired'] === '0')? $lang['acl:forever'] : date("Y-m-d H:i:s",$acls[$a][$A][$i]['expired']);
                                        ?><tr data-pressdo-acl-part><td data-pressdo-acl-part>
                                            <?=$i+1?></td><td data-pressdo-acl-part>
                                            <?=$acls[$a][$A][$i]['condition']?></td><td data-pressdo-acl-part>
                                            <?=$lang['acl:'.$acls[$a][$A][$i]['action']]?></td><td data-pressdo-acl-part>
                                            <?=$acls[$a][$A][$i]['expired']?></td><td data-pressdo-acl-part>
                                            <?php if($allow[$a] === true){ ?><button data-pressdo-button data-pressdo-button-red delete-info="<?=$a?>.<?=$acls[$a][$A][$i]['id']?>" msg="<?=$lang['msg:acl_delete_confirm']?>" type="button" class="d"><?=$lang['acl:delete']?></button></td><?php } ?>
                                        </tr><?php
                                    } ?>
                                </tbody>
                            </table>
                        </div><?php if($allow[$a] === true){ ?>
                        <form data-pressdo-acl-change method="post">
                            <input type="hidden" name="acl_target" value="<?=$a.'.'.$A?>">
                            <div data-pressdo-acl-change class="g">
                                <label data-pressdo-acl-change>Condition</label> 
                                <div data-pressdo-acl-change>
                                    <select id="cs" i="<?=$a.'-'.$A?>" name="target_type" data-pressdo-acl-change>
                                        <option data-pressdo-acl-change value="perm"><?=$lang['perm']?></option> 
                                        <option data-pressdo-acl-change value="member"><?=$lang['member']?></option> 
                                        <option data-pressdo-acl-change value="ip"><?=$lang['ip']?></option> 
                                        <option data-pressdo-acl-change value="geoip"><?=$lang['geoip']?></option> 
                                        <option data-pressdo-acl-change value="aclgroup"><?=$lang['aclgroup']?></option>
                                    </select> 
                                    <select id="ct" i="<?=$a.'-'.$A?>" name="target_name" v="inilne-block" data-pressdo-acl-change>
                                        <option data-pressdo-acl-change value="any"><?=$lang['perm:any']?></option> 
                                        <option data-pressdo-acl-change value="member"><?=$lang['perm:member']?></option> 
                                        <option data-pressdo-acl-change value="admin"><?=$lang['perm:admin']?></option> 
                                        <option data-pressdo-acl-change value="member_signup_15days_ago"><?=$lang['perm:member_singup_15days_ago']?></option> 
                                        <option data-pressdo-acl-change value="document_contributor"><?=$lang['perm:document_contributor']?></option> 
                                        <option data-pressdo-acl-change value="contributor"><?=$lang['perm:contributor']?></option> 
                                        <option data-pressdo-acl-change value="match_username_and_document_title"><?=$lang['perm:match_username_and_document_title']?></option>
                                    </select>
                                    <input id="ct_r" i="<?=$a.'-'.$A?>" name="" type="hidden" data-pressdo-acl-span data-pressdo-acl-change>
                                </div>
                            </div> 
                            <div data-pressdo-acl-change class="g">
                                <label data-pressdo-acl-change>Action :</label> 
                                <div data-pressdo-acl-change>
                                    <select name="action" data-pressdo-acl-change>
                                        <option data-pressdo-acl-change value="allow"><?=$lang['acl:allow']?></option> 
                                        <option data-pressdo-acl-change value="deny"><?=$lang['acl:deny']?></option> 
                                        <option data-pressdo-acl-change value="gotons"><?=$lang['acl:gotons']?></option>
                                    </select>
                                </div>
                            </div> 
                            <div data-pressdo-acl-change class="g">
                                <label data-pressdo-acl-change>Duration :</label> 
                                <div data-pressdo-acl-change>
                                    <span data-pressdo-acl-span data-pressdo-acl-change>
                                        <input id="dr" name="duration_raw" data-pressdo-acl-span type="hidden" name="0" value="0" i="<?=$a.'-'.$A?>">
                                        <select id="duration" data-pressdo-acl-span i="<?=$a.'-'.$A?>"> 
                                            <option data-pressdo-acl-span value="0"><?=$lang['acl:forever']?></option>
                                            <option data-pressdo-acl-span value="300"><?=$lang['acl:300s']?></option>
                                            <option data-pressdo-acl-span value="600"><?=$lang['acl:600s']?></option>
                                            <option data-pressdo-acl-span value="1800"><?=$lang['acl:1800s']?></option>
                                            <option data-pressdo-acl-span value="3600"><?=$lang['acl:3600s']?></option>
                                            <option data-pressdo-acl-span value="7200"><?=$lang['acl:7200s']?></option>
                                            <option data-pressdo-acl-span value="86400"><?=$lang['acl:86400s']?></option>
                                            <option data-pressdo-acl-span value="259200"><?=$lang['acl:259200s']?></option>
                                            <option data-pressdo-acl-span value="432000"><?=$lang['acl:432000s']?></option>
                                            <option data-pressdo-acl-span value="604800"><?=$lang['acl:604800s']?></option>
                                            <option data-pressdo-acl-span value="1209600"><?=$lang['acl:1209600s']?></option>
                                            <option data-pressdo-acl-span value="1814400"><?=$lang['acl:1814400s']?></option>
                                            <option data-pressdo-acl-span value="2419200"><?=$lang['acl:2419200s']?></option>
                                            <option data-pressdo-acl-span value="4838400"><?=$lang['acl:4838400s']?></option>
                                            <option data-pressdo-acl-span value="7257600"><?=$lang['acl:7257600s']?></option>
                                            <option data-pressdo-acl-span value="14515200"><?=$lang['acl:14515200s']?></option>
                                            <option data-pressdo-acl-span value="29030400"><?=$lang['acl:29030400s']?></option> 
                                            <option data-pressdo-acl-span value="raw"><?=$lang['acl:raw']?></option>
                                        </select>
                                        <input id="dr_e" data-pressdo-acl-span type="hidden" name="0" value="0" i="<?=$a.'-'.$A?>">
                                        <select id="du" data-pressdo-acl-span v="none" i="<?=$a.'-'.$A?>">
                                            <option data-pressdo-acl-span value="1"><?=$lang['acl:second']?></option>
                                            <option data-pressdo-acl-span value="60"><?=$lang['acl:minute']?></option>
                                            <option data-pressdo-acl-span value="3600"><?=$lang['acl:hour']?></option>
                                            <option data-pressdo-acl-span value="86400"><?=$lang['acl:day']?></option>
                                            <option data-pressdo-acl-span value="604800"><?=$lang['acl:week']?></option>
                                        </select>
                                    </span>
                                </div>
                            </div> 
                        <button data-pressdo-button data-pressdo-button-blue type="submit" class="s"><?=$lang['acl:add']?></button>
                    </form><?php }
                    } 
                    ?></div><?php
                }?>
                </div>
                <footer data-pressdo-con-footer><?php 
        }
        public static function login($args)
        {
            global $conf, $uri, $lang;
            list($redirect,$error) = $args;
            $msg = $lang['msg:'.$error];
            ?>
            <head>
                <title><?=$lang['login']?> - <?=$conf['SiteName']?></title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['login']?></h1>
            </div>
            <div data-pressdo-doc-content>
            <?php if($error){ ?>
                <div data-pressdo-alert-box class="a e">
                    <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                    <span data-pressdo-errorbox-innertext><?=$msg?></span>
                </div>
                <?php } ?>
                <form data-pressdo-register id="loginform" name="loginform" method="POST" action="<?=$uri['login_r'].$redirect?>">
                    <div data-pressdo-loginform class="c">
                        <label data-pressdo-loginform for="username">Username</label>
                        <input data-pressdo-formdata type="text" id="username" name="username" required>
                    </div>
                    <div data-pressdo-loginform class="c">
                        <label data-pressdo-loginform for="password">Password</label>
                        <input data-pressdo-formdata type="password" id="password" name="password" required>
                    </div>
                    <div data-pressdo-loginform class="b">
                        <label data-pressdo-loginform>
                            <input data-pressdo-formdata type="checkbox" name="autologin">
                            <span data-pressdo-formdata><?=$lang['autologin']?></span><!-- 자동로그인 미지원상태 -->
                        </label>
                    </div>
                    <a data-pressdo-loginform class="a" href="<?=$uri['recover_password']?>">[<?=$lang['recover_password']?>]</a>
                    <div data-pressdo-buttonarea>
                        <a data-pressdo-button href="<?=$uri['signup']?>"><?=$lang['signup']?></a>
                        <button type="submit" data-pressdo-button-blue data-pressdo-button id="log-in"><?=$lang['login']?></button>
                    </div>
                </form>
            </div>
            <footer data-pressdo-con-footer>
            <?php 
        }
        public static function signup($args)
        {
            list($mode,$err) = $args;
            global $conf, $uri, $lang;?>
            <head>
                <title><?=$lang['signup']?> - <?=$conf['SiteName']?></title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['signup']?></h1>
            </div>
            <div data-pressdo-doc-content>
                <?php if($err){ ?>
                    <div data-pressdo-alert-box class="a e">
                        <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                        <span data-pressdo-errorbox-innertext><?=$err?></span>
                    </div>
                <?php } 
            if($mode === 3){
                unset($_SESSION['MAIL_CHECK'])
                ?><p><?=str_replace('@1@', $_POST['username'], $lang['msg:welcome'])?></p><?php
            }elseif($mode === 2){ ?>
                <form data-pressdo-register id="signupform" name="signupform" method="POST" action="<?=$uri['signup']?>">
                    <div data-pressdo-loginform class="c">
                        <label data-pressdo-loginform for="username"><?=$lang['username']?></label>
                        <input data-pressdo-formdata type="text" id="username" name="username">
                        <p id="erruser1" class="errmsg" v="none"><?=str_replace('@1@', $lang['username'], $lang['msg:err_required_input'])?></p><p id="erruser2" class="errmsg" v="none"><?=$lang['msg:err_username_exists']?></p><p id="erruser3" class="errmsg" v="none"><?=$lang['msg:err_username_format']?></p>
                    </div>
                    <div data-pressdo-loginform class="c">
                        <label data-pressdo-loginform for="password"><?=$lang['password']?></label>
                        <input data-pressdo-formdata type="password" id="password" name="password">
                        <p id="errpwd" class="errmsg" v="none"><?=str_replace('@1@', $lang['password'], $lang['msg:err_required_input'])?></p>
                    </div>
                    <div data-pressdo-loginform class="c">
                        <label data-pressdo-loginform for="password2"><?=$lang['password2']?></label>
                        <input data-pressdo-formdata type="password" id="password2" name="password2">
                        <p id="errpwd21" class="errmsg" v="none"><?=str_replace('@1@', $lang['password2'], $lang['msg:err_required_input'])?></p><p id="errpwd22" class="errmsg" v="none"><?=$lang['msg:err_wrong_password2']?></p>
                    </div>
                    <b><?=$lang['msg:cannot_delete_account']?></b>
                    <button type="button" data-pressdo-button-blue data-pressdo-button-2 data-pressdo-button id="signup"><?=$lang['signup_submit']?></button>
                </form>
                <?php
            }elseif($mode === 1){ ?>
                <p><?=str_replace('@1@', $_POST['email'], $lang['msg:sent_email'])?></p>
                <ul>
                    <li><?=$lang['msg:check_spam']?></li>
                    <li><?=$lang['msg:email_expiry']?></li>
                </ul>
                <?php
            }elseif($mode === 0 || $mode === null){
                $_SESSION['MAIL_CHECK'] = true;?>
                <form data-pressdo-register method="POST" id="regform" name="regform" action="<?=$uri['signup']?>">
                    <div data-pressdo-loginform class="c">
                        <label data-pressdo-loginform for="email"><?=$lang['email']?></label>
                        <input data-pressdo-formdata type="email" id="email" name="email">
                        <p id="errmail" class="errmsg" v="none"><?=str_replace('@1@', $lang['email'], $lang['msg:err_required_input'])?></p>
                        <?php
                        if($conf['UseMailWhitelist'] === true){
                            ?><p><?=$lang['msg:use_email_whitelist']?></p>
                            <ul data-pressdo-mail-whitelist><?php
                            foreach($conf['MailWhitelist'] as $wl){
                                ?><li data-pressdo-mail-whitelist><?=$wl?></li><?php
                            }
                            ?></ul><?php
                        } ?>
                    </div>
                    <b><?=$lang['msg:cannot_delete_account']?></b>
                    <button type="button" data-pressdo-button-blue data-pressdo-button-2 data-pressdo-button id="chkmail"><?=$lang['signup_submit']?></button>
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
            <footer data-pressdo-con-footer><?php
        }
        public static function admin($args)
        {
            list($type,$data) = $args;
            global $conf, $lang, $uri;
            if(!$type)
                Header("HTTP/1.0 404 Not Found");
            if($type == 'grant'){ ?>
            <head><title><?=$lang['grant']?> - <?=$conf['SiteName']?></title></head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['grant']?></h1>
            </div>
            <div data-pressdo-doc-content>
                <?php if($data['errbox']){ ?>
                <div data-pressdo-alert-box class="a e">
                    <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                    <span data-pressdo-errorbox-innertext><?=$data['errbox']?></span>
                </div>
                <?php } ?>
                <form method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                    Username <input data-pressdo-acl-span type="text" name="username">
                    <div data-pressdo-buttonarea>
                        <button data-pressdo-button-blue data-pressdo-button type="submit"><?=$lang['confirm']?></button>
                    </div>
                </form>
                <?php if($_GET['username']){ ?>
                <form method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                <ul data-pressdo-grant>
                    <h3><?=$_GET['username']?><input type="hidden" name="member" value="<?=$_GET['username']?>"></h3>
                    <?php foreach($data['perms'] as $p){
                        if(in_array($p, $data['have']))
                            $checked = 'checked';
                        else
                            $checked = '';
                        ?><li><?=$p?> <input type="checkbox" name="perms[]" value="<?=$p?>" <?=$checked?>></li><?php
                    } ?>
                    
                </ul>
                <div data-pressdo-buttonarea>
                    <button data-pressdo-button-blue data-pressdo-button type="submit"><?=$lang['confirm']?></button>
                </div>
                </form>
                <?php } ?>
            </div>
            <?php }elseif($type == 'login_history'){ 
            if($conf['UseShortURI'] === true)
            $p_ = (!$data['until'])?'data-pressdo-history-null' : 'href='.$uri['login_history'].$Doc.$uri['prefix'].'username='.$_GET['username'].'&until='.$data['until'];
            $f_ = (!$data['from'])?'data-pressdo-history-null' : 'href='.$uri['login_history'].$Doc.$uri['prefix'].'username='.$_GET['username'].'&from='.$data['from']; ?>
            <head><title><?=$lang['login_history']?> - <?=$conf['SiteName']?></title></head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['login_history']?></h1>
            </div>
            <div data-pressdo-doc-content>
                <?php if($data['errbox']){ ?>
                <div data-pressdo-alert-box class="a e">
                    <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                    <span data-pressdo-errorbox-innertext><?=$data['errbox']?></span>
                </div>
                <?php } ?>
                <form method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                    <div data-pressdo-loginform class="c">
                        <label data-pressdo-loginform for="username"><?=$lang['username']?> : </label>
                        <input data-pressdo-acl-span type="text" id="username" name="username">
                    </div>
                    <div data-pressdo-buttonarea>
                        <button data-pressdo-button-blue data-pressdo-button type="submit"><?=$lang['confirm']?></button>
                    </div>
                </form>
                <?php if($_GET['username']){ ?>
                <form method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                    <h3><?=$_GET['username']?> <?=$lang['login_history']?></h3><br>
                    <p><?=$lang['last_login_ua'].' : '.$data['UA']?></p><br>
                    <p><?=$lang['email'].' : '.$data['email']?></p>
                    <div data-pressdo-toolbar-menu>
                        <a <?=$p_?> data-pressdo-history-mv>
                            <span ionicon ion-arrow-back></span> Prev
                        </a>
                        <a <?=$f_?> data-pressdo-history-mv>
                            Next <span ionicon ion-arrow-forward></span>
                        </a>
                    </div>
                    <table data-pressdo-doc-table>
                        <thead>
                            <th><strong>Date</strong></th>
                            <th><strong>IP</strong></th>
                        </thead>
                    <?php foreach($data['history'] as $h){ ?>
                        <tr>
                            <td><?=date('Y-m-d H:i:s', $h['datetime'])?></td>
                            <td><?=$h['ip']?></td>
                        </tr><?php
                    } ?>
                    </table>
                    <div data-pressdo-toolbar-menu>
                        <a <?=$p_?> data-pressdo-history-mv>
                            <span ionicon ion-arrow-back></span> Prev
                        </a>
                        <a <?=$f_?> data-pressdo-history-mv>
                            Next <span ionicon ion-arrow-forward></span>
                        </a>
                    </div>
                </form>
                <?php } ?>
            </div>
            <?php }
            ?><footer data-pressdo-con-footer><?php 
        }
        public static function RecentPage($args)
        {
            list($page,$h) = $args;
            global $conf, $lang, $uri, $_ns;
            ($conf['UseShortURI'] === true)? $d='?':$d='&'; ?>
            <head>
                    <title> <?=$lang[$page]?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang[$page]?></h1>
            </div>
            <div data-pressdo-doc-content>
                <div>
                    <ol data-pressdo-recentmenu><?php
                    if($page == 'RecentChanges'){ ?>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=all">[전체]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=create">[새 문서]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=delete">[삭제]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=move">[이동]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentChanges'].$d?>logtype=revert">[되돌림]</a></li>
                    <?php }elseif($page == 'RecentDiscuss'){ ?>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentDiscuss'].$d?>logtype=normal_thread">[열린 토론]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentDiscuss'].$d?>logtype=old_thread">[오래된 토론]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentDiscuss'].$d?>logtype=closed_thread">[닫힌 토론]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentDiscuss'].$d?>logtype=open_editrequest">[열린 편집 요청]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentDiscuss'].$d?>logtype=accepted_editrequest">[승인된 편집 요청]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentDiscuss'].$d?>logtype=closed_editrequest">[닫힌 편집 요청]</a></li>
                        <li data-pressdo-recentmenu><a href="<?=$uri['RecentDiscuss'].$d?>logtype=old_editrequest">[오래된 편집 요청]</a></li>
                    <?php } ?>
                    </ol>
                    <table data-pressdo-recent-changes>
                        <colgroup>
                            <col>
                            <?php if($page == 'RecentChanges'){ ?><col style="width: 25%;"><?php } ?>
                            <col style="width: 22%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th data-pressdo-recent-changes>항목</th>
                                <?php if($page == 'RecentChanges'){ ?><th data-pressdo-recent-changes>수정자</th><?php } ?>
                                <th data-pressdo-recent-changes>수정 시간</th>
                            </tr>
                        </thead>
                        <tbody><?php
                        if($page == 'RecentChanges'){
                            foreach($h as $he) {
                                ($he['document']['namespace'] == $_ns['document'] && $conf['ForceShowNameSpace'] === false)? $ns = '':$ns = $he['document']['namespace'].':';
                                $title = $ns.$he['document']['title'];
                                $Diff = $he['count'];

                                // 바이트 수 차이 색깔 표시
                                if ($Diff > 0)
                                    $Diff = '(<span data-pressdo-history-green>+'.$Diff.'</span>)';
                                elseif ($Diff == 0)
                                    $Diff = '(<span data-pressdo-history-gray>0</span>)';
                                elseif ($Diff < 0)
                                    $Diff = '(<span data-pressdo-history-red>'.$Diff.'</span>)';
                                ?>
                            <tr data-pressdo-recent-changes class="n">
                                <td data-pressdo-recent-changes>
                                    <a href="<?=$uri['wiki'].$title?>"><?=$title?></a> 
                                    <a href="<?=$uri['history'].$title?>">[역사]</a>
                                    <?php if($he['logtype'] !== 'create'){ ?><a href="<?=$uri['diff'].$title.$d.'rev='.$he['rev']?>">[비교]</a><?php } ?>
                                    <a href="<?=$uri['discuss'].$title?>">[토론]</a>
                                    <span><?=$Diff?></span>
                                </td>
                                <td data-pressdo-recent-changes><div data-pressdo-popoever><?php 
                                if($he['author'] === null){
                                    ?><a data-pressdo-recent-user style="<?=$he['style']?>" data-pressdo-user-ip><?=$he['ip']?></a><?php
                                }else{
                                    ?><a data-pressdo-recent-user style="<?=$he['style']?>" data-pressdo-user-member><?=$he['author']?></a></div>
                                <?php } ?></div></td>
                                <td data-pressdo-recent-changes><time datetime="<?=date('Y-m-d\TH:i:s', $he['date']-32400)?>"><?=date('Y-m-d H:i:s', $he['date'])?></time></td>
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
                            <tr data-pressdo-recent-changes>
                                <td data-pressdo-recent-changes colspan="3">
                                    <span data-pressdo-recent-changes><?=$he['log']?></span>
                                    <i><?=$italic?></i>
                                </td>
                            </tr><?php
                                } 
                            }
                        }elseif($page == 'RecentDiscuss'){ 
                            foreach($h as $he) { 
                                ($he['document']['namespace'] == $_ns['document'] && $conf['ForceShowNameSpace'] === false)? $ns = '':$ns = $he['document']['namespace'].':'; ?>
                            <tr data-pressdo-recent-changes class="n">
                                <td data-pressdo-recent-changes>
                                    <a href="<?=$uri['thread'].$he['slug']?>"><?=$he['topic']?></a> (<a href="<?=$uri['discuss'].$ns.$he['document']['title']?>"><?=$ns.$he['document']['title']?></a>)
                                </td>
                                <td data-pressdo-recent-changes><time datetime="<?=date('Y-m-d\TH:i:s', $he['date']-32400)?>"><?=date('Y-m-d H:i:s', $he['date'])?></time></td>
                            </tr>
                        <?php }
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php 
        }
        public static function aclgroup($args)
        {
            list($err, $api) = $args;
            global $conf, $lang, $uri;
            $current = $api['currentgroup'];
            $groups = $api['aclgroups'];
            ($conf['UseShortURI'] === true)? $d='?':$d='&'; 
            $p_ = (!$api['until'])?'data-pressdo-history-null' : 'href='.$uri['aclgroup'].$Doc.$uri['prefix'].'until='.$api['until'];
            $f_ = (!$api['from'])?'data-pressdo-history-null' : 'href='.$uri['aclgroup'].$Doc.$uri['prefix'].'from='.$api['from'];
            if(!($api['accessible'] === true)){
                $st = 'disabled="disabled"';
                $tool = 'off';
            } ?>
            <head><title>ACLGroup - <?=$conf['SiteName']?></title></head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title>ACLGroup</h1>
            </div>
            <div data-pressdo-doc-content>
                <?php if($err[0]){ ?>
                <div data-pressdo-alert-box class="a e">
                    <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                    <span data-pressdo-errorbox-innertext><?=$lang['msg:'.$err[0]]?></span>
                </div>
                <?php } ?>
                <ul data-pressdo-aclgroups><?php
                    foreach ($groups as $g){ 
                        ($current == $g)? $cls = 'a':$cls = '';?>
                    <li data-pressdo-aclgroup>
                        <a data-pressdo-aclgroup-link class="<?=$cls?>" href="<?=$uri['aclgroup'].$d.'group='.$g?>"><?=$g?><?php if($tool != 'off'){ ?><button data-pressdo-aclgroup-link d_target="<?=$g?>" class="rmag" msg="<?=str_replace('@1@', $g, $lang['msg:aclgroup_delete'])?>" type="button">×</button><?php } ?></a>
                    </li><?php } 
                    if($tool != 'off'){ ?>
                    <li data-pressdo-aclgroup>
                        <button data-pressdo-aclgroup-link id="nag" type="button">+</button>
                    </li>
                    <?php } ?>
                </ul>
                <form data-pressdo-aclgroup method="POST" action="<?=$_SERVER['REQUEST_URI']?>" class="a">
                    <input data-pressdo-aclgroup data-pressdo-acl-span type="hidden" name="group" value="<?=$current?>">
                    <div data-pressdo-aclgroup class="g">
                        <select data-pressdo-aclgroup data-pressdo-acl-span name="mode" id="agmd">
                            <option value="ip"><?=$lang['ip']?></option>
                            <option value="username"><?=$lang['username']?></option>
                        </select>
                        <input data-pressdo-acl-span data-pressdo-aclgroup id="vl" type="text" name="ip" placeholder="CIDR">
                        <?php if($err[1]['username']) {?><p class="errmsg"><?=$lang['msg:err_wrong_username']?></p><?php } ?>
                        <?php if($err[1]['ip']) {?><p class="errmsg"><?=$lang['msg:invalid_cidr']?></p><?php } ?>
                    </div>
                    <div data-pressdo-aclgroup class="g">
                        <label data-pressdo-aclgroup for="noteInput"><?=$lang['memo']?> :</label>
                        <input data-pressdo-acl-span data-pressdo-aclgroup type="text" id="noteInput" name="note">
                        <?php if($err[1]['note']) {?><p id="erruser" class="errmsg"><?=str_replace('@1@', 'note', $lang['msg:err_required_input'])?></p><?php } ?>
                    </div>
                    <div data-pressdo-aclgroup class="g">
                        <label data-pressdo-aclgroup><?=$lang['expire']?> :</label>
                        <span>
                            <input id="dr" name="duration_raw" data-pressdo-acl-span="" type="hidden" value="0" i="aclgroup">
                            <select data-pressdo-acl-span id="dur" i="aclgroup">
                                <option data-pressdo-acl-span value="0"><?=$lang['acl:forever']?></option>
                                <option data-pressdo-acl-span value="300"><?=$lang['acl:300s']?></option>
                                <option data-pressdo-acl-span value="600"><?=$lang['acl:600s']?></option>
                                <option data-pressdo-acl-span value="1800"><?=$lang['acl:1800s']?></option>
                                <option data-pressdo-acl-span value="3600"><?=$lang['acl:3600s']?></option>
                                <option data-pressdo-acl-span value="7200"><?=$lang['acl:7200s']?></option>
                                <option data-pressdo-acl-span value="86400"><?=$lang['acl:86400s']?></option>
                                <option data-pressdo-acl-span value="259200"><?=$lang['acl:259200s']?></option>
                                <option data-pressdo-acl-span value="432000"><?=$lang['acl:432000s']?></option>
                                <option data-pressdo-acl-span value="604800"><?=$lang['acl:604800s']?></option>
                                <option data-pressdo-acl-span value="1209600"><?=$lang['acl:1209600s']?></option>
                                <option data-pressdo-acl-span value="1814400"><?=$lang['acl:1814400s']?></option>
                                <option data-pressdo-acl-span value="2419200"><?=$lang['acl:2419200s']?></option>
                                <option data-pressdo-acl-span value="4838400"><?=$lang['acl:4838400s']?></option>
                                <option data-pressdo-acl-span value="7257600"><?=$lang['acl:7257600s']?></option>
                                <option data-pressdo-acl-span value="14515200"><?=$lang['acl:14515200s']?></option>
                                <option data-pressdo-acl-span value="29030400"><?=$lang['acl:29030400s']?></option> 
                                <option data-pressdo-acl-span value="raw"><?=$lang['acl:raw']?></option>
                            </select>
                            <input id="dr_e" data-pressdo-acl-span type="hidden" name="0" value="0" i="aclgroup">
                            <select id="du" data-pressdo-acl-span v="none" i="aclgroup">
                                <option data-pressdo-acl-span value="1"><?=$lang['acl:second']?></option>
                                <option data-pressdo-acl-span value="60"><?=$lang['acl:minute']?></option>
                                <option data-pressdo-acl-span value="3600"><?=$lang['acl:hour']?></option>
                                <option data-pressdo-acl-span value="86400"><?=$lang['acl:day']?></option>
                                <option data-pressdo-acl-span value="604800"><?=$lang['acl:week']?></option>
                            </select>
                        </span>
                        <?php if($err[1]['expire']) {?><p class="errmsg"><?=str_replace('@1@', 'expire', $lang['msg:err_required_input'])?></p><?php } ?>
                        <?php if($err[1]['expire_much']) {?><p class="errmsg"><?=$lang['msg:err_expire_too_long']?></p><?php } ?>
                    </div>
                    <button data-pressdo-button data-pressdo-button-blue <?=$st?> type="submit"><?=$lang['acl:add']?></button>
                </form>
                <div data-pressdo-toolbar-menu>
                    <a <?=$p_?> data-pressdo-history-mv>
                        <span ionicon ion-arrow-back></span> Prev
                    </a>
                    <a <?=$f_?> data-pressdo-history-mv>
                        Next <span ionicon ion-arrow-forward></span>
                    </a>
                </div>
                <form class="s" data-pressdo-aclgroup method="GET">
                    <div data-pressdo-aclgroup class="g">
                        <input data-pressdo-acl-span type="text" name="from" placeholder="ID">
                        <button data-pressdo-button data-pressdo-button-blue type="submit">Go</button>
                    </div>
                </form>
                <table data-pressdo-aclgroup>
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
                            <th data-pressdo-recent-changes data-pressdo-aclgroup>ID</th>
                            <th data-pressdo-recent-changes data-pressdo-aclgroup><?=$lang['acl:target']?></th>
                            <th data-pressdo-recent-changes data-pressdo-aclgroup><?=$lang['memo']?></th>
                            <th data-pressdo-recent-changes data-pressdo-aclgroup><?=$lang['aclgroup:added']?></th>
                            <th data-pressdo-recent-changes data-pressdo-aclgroup><?=$lang['aclgroup:expired']?></th>
                            <th data-pressdo-recent-changes data-pressdo-aclgroup><?=$lang['aclgroup:action']?></th>
                        </tr>
                    </thead>
                    <tbody><?php 
                        if(count($api['groupmembers']) === 0){
                            ?><td data-pressdo-aclgroup class="s" colspan="5"><?=$lang['msg:empty_aclgroup']?></td><?php 
                        }else{
                            foreach($api['groupmembers'] as $li){
                                ($li['expiry'] == 0)? $e = $lang['acl:forever']:$e = date('Y-m-d H:i:s', $li['expiry']);
                                ?><tr>
                                    <td data-pressdo-aclgroup class="s"><?=$li['id']?></td>
                                    <td data-pressdo-aclgroup class="s"><?=$li['cidr'].$li['member']?></td>
                                    <td data-pressdo-aclgroup class="s"><?=$li['memo']?></td>
                                    <td data-pressdo-aclgroup class="s"><?=date('Y-m-d H:i:s', $li['date'])?></td>
                                    <td data-pressdo-aclgroup class="s"><?=$e?></td>
                                    <td data-pressdo-aclgroup><button <?=$st?> id="acl_delete" acllog_id="<?=$li['id']?>" data-pressdo-button data-pressdo-button-red type="button"><?=$lang['acl:delete']?></button></td>
                                </tr><?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <div data-pressdo-toolbar-menu>
                    <a <?=$p_?> data-pressdo-history-mv>
                        <span ionicon ion-arrow-back></span> Prev
                    </a>
                    <a <?=$f_?> data-pressdo-history-mv>
                        Next <span ionicon ion-arrow-forward></span>
                    </a>
                </div>
                <?php if($err[1]['mode'] == 'd'){ ?><div id="remove_win" data-pressdo-overlay style="display:block;"><?php }else{ ?>
                <div id="remove_win" data-pressdo-overlay v="none"><?php } ?>
                    <div data-pressdo-overlay-onclick>
                        <div data-pressdo-overlay-topright></div>
                        <div data-pressdo-overlay-box>
                            <?php if($err[0]){ ?>
                            <div data-pressdo-alert-box class="a e">
                                <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                                <span data-pressdo-errorbox-innertext><?=$err[0]?></span>
                            </div>
                            <?php } ?>
                            <form data-pressdo-aclgroup-remove method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                                <input data-pressdo-aclgroup data-pressdo-acl-span id="idto_remove" type="hidden" name="id" value>
                                <input data-pressdo-aclgroup data-pressdo-acl-span type="hidden" name="group" value="<?=$current?>">
                                <h4 data-pressdo-aclgroup-remove><?=$lang['aclgroup:remove_member']?></h4>
                                <div>
                                    <p data-pressdo-aclgroup-remove>ID:</p>
                                    <span id="id_to_remove"></span>
                                </div>
                                <div>
                                    <p data-pressdo-aclgroup-remove><?=$lang['memo']?>:</p>
                                    <input data-pressdo-aclgroup data-pressdo-acl-span type="text" name="note">
                                    <?php if($err[1]['note']) {?><p id="erruser" class="errmsg"><?=str_replace('@1@', 'note', $lang['msg:err_required_input'])?></p><?php } ?>
                                </div>
                                <div data-pressdo-buttons-right>
                                    <button data-pressdo-button data-pressdo-button-blue data-pressdo-button-sizeup type="submit"><?=$lang['acl:delete']?></button>
                                    <button data-pressdo-button data-pressdo-button-sizeup id="cancel_btn" type="button"><?=$lang['cancel']?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php if($err[1]['mode'] == 'c'){ ?><div id="add_win" data-pressdo-overlay style="display:block;"><?php }else{ ?>
                <div id="add_win" data-pressdo-overlay v="none"><?php } ?>
                    <div data-pressdo-overlay-onclick>
                        <div data-pressdo-overlay-topright></div>
                        <div data-pressdo-overlay-box>
                            <?php if($err[0]){ ?>
                            <div data-pressdo-alert-box class="a e">
                                <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                                <span data-pressdo-errorbox-innertext><?=$err[0]?></span>
                            </div>
                            <?php } ?>
                            <form data-pressdo-aclgroup-remove method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                                <h4 data-pressdo-aclgroup-remove><?=$lang['aclgroup:group_add']?></h4>
                                <div>
                                    <p data-pressdo-aclgroup-remove><?=$lang['aclgroup:group_name']?>:</p>
                                    <input data-pressdo-aclgroup data-pressdo-acl-span type="text" name="name">
                                    <?php if($err[1]['name']) {?><p id="erruser" class="errmsg"><?=str_replace('@1@', 'name', $lang['msg:err_required_input'])?></p><?php } ?>
                                    <br><input type="checkbox" name="isAdmin[]" value="true"> 관리자만 보이기
                                </div>
                                <div data-pressdo-buttons-right>
                                    <button data-pressdo-button data-pressdo-button-blue data-pressdo-button-sizeup type="submit"><?=$lang['btn:create']?></button>
                                    <button data-pressdo-button data-pressdo-button-sizeup id="cancel_btn" type="button"><?=$lang['cancel']?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php 
        }
        public static function discuss($args)
        {
            list($Doc, $NS, $Title, $EditRequests, $ThreadList, $err) = $args;
            global $conf, $lang, $uri;
            ($conf['UseShortURI'] === true)? $d='?':$d='&';?>
            <head>
            <title> <?=$Doc?> (<?=$lang['discuss']?>) - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a>
                    <small data-pressdo-doc-action>(<?=$lang['discuss']?>)</small>
                </h1>
            </div>
            <div id="cont_ent" data-pressdo-doc-content>
            <?php if($err){ 
                if(is_array($err))
                    $err = str_replace(['@1@','@2@'], [$err[1], $err[2]], $lang['msg:'.$err[0]]);
                else
                    $err = $lang['msg:'.$err];
                ?>
                <div data-pressdo-alert-box class="a e">
                    <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                    <span data-pressdo-errorbox-innertext><?=$err?></span>
                </div>
                <?php } ?>
                <div>
                    <h3 data-pressdo-discuss-type><?=$lang['edit_request']?></h3>
                    <ul data-pressdo-ul></ul>
                    <p data-pressdo-discuss-more><a href="<?=$uri['discuss'].$Doc.$d.'state=closed_edit_requests'?>">[<?=$lang['show_closed_edit_requests']?>]</a></p>
                    <h3 data-pressdo-discuss-type><?=$lang['discuss']?></h3>
                    <ul data-pressdo-ul><?php 
                    $cnt = count($ThreadList);
                    for($i=0; $i < $cnt; ++$i){ ?>
                        <li>
                            <a href="#s-<?=$i+1?>"><?=$i+1?></a>.
                            <a href="<?=$uri['thread'].$ThreadList[$i]['slug']?>"><?=$ThreadList[$i]['topic']?></a>
                        </li><?php
                    }
                    ?></ul>
                    <p data-pressdo-discuss-more><a href="<?=$uri['discuss'].$Doc.$d.'state=close'?>">[<?=$lang['show_closed_discuss']?>]</a></p><?php
                    for($i=0; $i<$cnt; ++$i){ ?>
                        <div>
                            <h2><?=$i+1?>. <a id="s-<?=$i+1?>" href="<?=$uri['thread'].$ThreadList[$i]['slug']?>"><?=$ThreadList[$i]['topic']?></a></h2>
                            <div class="t">
                                <?php foreach ($ThreadList[$i]['discuss'] as $d){ 
                                    $str = ($d['admin'])? 'data-pressdo-user-member':''; 
                                    if($d['type'] == 'status'){
                                        $sta = 's';
                                        $con = str_replace('@1@', $d['text'], $lang['msg:changed_thread_status']);
                                    }elseif($d['type'] == 'document'){
                                        $sta = 's';
                                        $con = str_replace('@1@', $d['text'], $lang['msg:changed_thread_document']);
                                    }elseif($d['type'] == 'topic'){
                                        $sta = 's';
                                        $con = str_replace('@1@', $d['text'], $lang['msg:changed_thread_topic']);
                                    }else{
                                        $sta = '';
                                        $con = $d['text']; 
                                    }
                                    
                                    if($d['id'] == $ThreadList[$i]['discuss'][1]['id'] && $d['id'] > 2){ ?>
                                        <a data-pressdo-thread-content href="<?=$uri['thread'].$ThreadList[$i]['slug']?>" class="m">more...</a>
                                    <?php } ?>
                                    <div data-pressdo-thread-content class="r v">
                                        <div data-pressdo-thread-content class="c">
                                            <div data-pressdo-thread-content class="h">
                                                <span><a id="<?=$d['id']?>">#<?=$d['id']?></a></span>
                                                <div v="inline">
                                                    <div v="inline-block">
                                                        <a <?=$str?>><?=$d['author'].$d['ip']?></a>
                                                    </div>
                                                </div>
                                                <span class="d"><time datetime="<?=date('Y-m-d\TH:i:s', $d['date']).'.000Z'?>"><?=date('Y-m-d H:i:s', $d['date'])?></time></span>
                                            </div>
                                            <div data-pressdo-thread-content class="b <?=$sta?>">
                                                <div>
                                                    <div data-pressdo-thread-content class="w">
                                                        <div data-pressdo-doc-paragraph><?=$con?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div><?php
                    } ?>
                    <h3 data-pressdo-discuss-type><?=$lang['create_thread']?></h3><?php
                    if($Doc == $conf['FrontPage']){ ?>
                    <div data-pressdo-alert-box class="a">
                        <strong><?=$lang['msg:warning']?></strong>
                        <?=str_replace('@1@', $conf['FrontPage'], $lang['msg:discuss_in_frontpage'])?>
                    </div>
                    <?php } ?>
                    <form method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                        <div data-pressdo-loginform class="c">
                            <label data-pressdo-breakline for="topicInput"><?=$lang['topic']?> :</label>
                            <input data-pressdo-thread data-pressdo-wideinput type="text" id="topicInput" name="topic">
                        </div>
                        <div data-pressdo-loginform class="c">
                            <label data-pressdo-breakline for="contentInput"><?=$lang['content']?> :</label>
                            <textarea data-pressdo-thread name="text" id="contentInput" rows="5"></textarea>
                            <?php if(!$_SESSION['member']['username']){ ?>
                            <p data-pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_create'])?></p><?php } ?>
                        </div>
                        <button type="submit" data-pressdo-button data-pressdo-button-wideright data-pressdo-button-blue><?=$lang['submit']?></button>
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
                    </form method="POST">
                </div>
            </div>
            <footer data-pressdo-con-footer><?php 
        }
        public static function thread($args)
        {
            list($ns, $title, $dat, $err) = $args;
            global $conf, $lang, $uri;
            $status = ['normal', 'pause', 'close'];

            ($conf['UseShortURI'] === true)? $d='?':$d='&';?>
            <head>
                <title> <?=$ns.$title?> (<?=$lang['discuss']?>) - <?=$dat['topic']?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><a href="<?=$uri['wiki'].$ns.$title?>"><span data-pressdo-title-namespace><?=$ns?></span><?=$title?></a>
                    <small data-pressdo-doc-action>(<?=$lang['discuss']?>)</small>
                </h1>
            </div>
            <div id="cont_ent" data-pressdo-doc-content>
            <?php if($err){ 
                if(is_array($err))
                    $err = str_replace(['@1@','@2@'], [$err[1], $err[2]], $lang['msg:'.$err[0]]);
                else
                    $err = $lang['msg:'.$err];
                ?>
                <div data-pressdo-alert-box class="a e">
                    <strong data-pressdo-errorbox-innertext><?=$lang['msg:error']?></strong>
                    <span data-pressdo-errorbox-innertext><?=$err?></span>
                </div>
                <?php } ?>
                <div>
                    <form data-pressdo-thread-show method="POST" action="<?=$_SERVER['REQUEST_URI']?>" class="d">
                        <input type="checkbox" id="noDisplayHideAuthor"><label for="noDisplayHideAuthor"><?=$lang['msg:noDisplayHideAuthor']?></label>
                    </form>
                    <h2 data-pressdo-thread-topic><?=$dat['topic']?></h2>
                        <div>
                            <div class="t">
                                <?php foreach ($dat['comments'] as $d){ 
                                    $str = ($d['admin'])? 'data-pressdo-user-member':''; 
                                    if($d['type'] == 'status'){
                                        $sta = 's';
                                        $con = str_replace('@1@', $d['text'], $lang['msg:changed_thread_status']);
                                    }elseif($d['type'] == 'document'){
                                        $sta = 's';
                                        $con = str_replace('@1@', $d['text'], $lang['msg:changed_thread_document']);
                                    }elseif($d['type'] == 'topic'){
                                        $sta = 's';
                                        $con = str_replace('@1@', $d['text'], $lang['msg:changed_thread_topic']);
                                    }else{
                                        $sta = '';
                                        $con = $d['text']; 
                                    }
                                    
                                    if($d['author'] == $dat['initial_author'] || $d['ip'] == $dat['initial_author'])
                                        $aut = 'f';
                                    else
                                        $aut = ''; ?>
                                    <div data-pressdo-thread-content class="r v">
                                        <div data-pressdo-thread-content class="c">
                                            <div data-pressdo-thread-content class="h <?=$aut?>">
                                                <span><a id="<?=$d['id']?>">#<?=$d['id']?></a></span>
                                                <div v="inline">
                                                    <div v="inline-block">
                                                        <a <?=$str?>><?=$d['author'].$d['ip']?></a>
                                                    </div>
                                                </div>
                                                <span class="d">
                                                    <time datetime="<?=date('Y-m-d\TH:i:s', $d['date']).'.000Z'?>"><?=date('Y-m-d H:i:s', $d['date'])?></time>
                                                    <div v="inline" class="p">
                                                        <div v="inline-block">
                                                            <button data-pressdo-thread-button data-pressdo-button></button>
                                                        </div>
                                                    </div>
                                                </span>
                                                <div data-pressdo-thread-content class="f"></div>
                                            </div>
                                            <div data-pressdo-thread-content class="b <?=$sta?>">
                                                <div>
                                                    <div data-pressdo-thread-content class="w">
                                                        <div data-pressdo-doc-paragraph><?=$con?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <h3 data-pressdo-discuss-type><?=$lang['write_thread_comment']?></h3><?php
                    if($dat['updateThreadStatus'] === true){ ?>
                        <form method="POST">
                            [ADMIN] <?=$lang['updateThreadStatus']?>
                            <select name="status"><?php
                                foreach($status as $s){
                                    if($s != $dat['status']){
                                        ?><option value="<?=$s?>"><?=$s?></option><?php
                                    }
                                }
                            ?></select>
                            <button data-pressdo-button type="submit"><?=$lang['change']?></button>
                        </form>
                    <?php }
                    if($dat['updateThreadDocument'] === true){ ?>
                        <form method="POST">
                            [ADMIN] <?=$lang['updateThreadDocument']?>
                            <input type="text" name="document" value="<?=$ns.$title?>">
                            <button data-pressdo-button type="submit"><?=$lang['change']?></button>
                        </form>
                    <?php }
                    if($dat['updateThreadTopic'] === true){ ?>
                        <form method="POST">
                            [ADMIN] <?=$lang['updateThreadTopic']?>
                            <input type="text" name="topic" value="<?=$dat['topic']?>">
                            <button data-pressdo-button type="submit"><?=$lang['change']?></button>
                        </form>
                    <?php } ?>
                    <form method="POST">
                        <div data-pressdo-loginform class="c">
                            <textarea data-pressdo-thread id="contentInput" rows="5" <?=($dat['status']!='normal'?' disabled="disabled"':'name="text"')?> required><?=($dat['status']!='normal'?$lang['msg:thread_status_'.$dat['status']]:'')?></textarea>
                            <?php if(!$_SESSION['member']['username']){ ?>
                            <p data-pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_comment'])?></p><?php } ?>
                        </div>
                        <button type="submit" <?=($dat['status']!='normal'?' disabled="disabled"':'')?> data-pressdo-button data-pressdo-button-wideright data-pressdo-button-blue><?=$lang['submit']?></button>
                        <div style="clear: both;">
                            <div class="grecaptcha-badge" data-style="inline" style="width: 256px; height: 60px; box-shadow: gray 0px 0px 5px;">
                                <div class="grecaptcha-logo">
                                    <iframe title="reCAPTCHA" src="https://www.google.com/recaptcha/api2/anchor?ar=1&k=6Le0YCgUAAAAAPuP955bk3npzh_ymfSd53DpI74j&co=aHR0cHM6Ly90aGVzZWVkLmlvOjQ0Mw..&hl=ko&v=6OAif-f8nYV0qSFmq-D6Qssr&size=invisible&badge=inline&cb=8wsokgsumi1k" width="256" height="60" role="presentation" name="a-p1qimm5s15r" frameborder="0" scrolling="no" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation allow-modals allow-popups-to-escape-sandbox"></iframe>
                                </div>
                                <div class="grecaptcha-error"></div>
                                <textarea id="g-recaptcha-response-4" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid rgb(193, 193, 193); margin: 10px 25px; padding: 0px; resize: none; display: none;"></textarea>
                            </div>
                            <iframe style="display: none;"></iframe>
                        </div>
                    </form>
                </div>
            </div>
            <div data-pressdo-thread-popover="hidden" placement="bottom" class="tooltip popover">
                <div class="wrapper">
                    <div class="tooltip-inner popover-inner" style="position:relative;">
                        <div>
                            <div class="m">
                                <button id="bt-wiki" v="block" data-pressdo-button type="button"><?=$lang['view_original']?></button>
                                <button id="bt-origin" v="none" data-pressdo-button type="button"><?=$lang['view_wiki']?></button>
                                <button data-pressdo-button data-pressdo-button-red type="button">[ADMIN] <?=$lang['hide_thread_comment']?></button>
                            </div>
                        </div>
                    </div>
                    <div class="tooltip-arrow popover-arrow" style="left:66px;"></div>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php 
        }
        public static function BlockHistory($args)
        {
            $data = $args[0];
            global $conf, $lang, $uri;
            if($conf['UseShortURI'] === true)
            $p_ = (!$data['prev_page'])?'data-pressdo-history-null' : 'href='.$uri['BlockHistory'].$Doc.$uri['prefix'].'until='.$data['prev_page'];
            $f_ = (!$data['next_page'])?'data-pressdo-history-null' : 'href='.$uri['BlockHistory'].$Doc.$uri['prefix'].'from='.$data['next_page']; ?>
            <head><title><?=$lang['BlockHistory']?> - <?=$conf['SiteName']?></title></head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['BlockHistory']?></h1>
            </div>
            <div data-pressdo-doc-content>
                <div>
                    <form method="GET">
                        <input type="hidden" name="page" value="BlockHistory">
                        <select name="target">
                            <option value="text"><?=$lang['text']?></option>
                            <option value="author"><?=$lang['author']?></option>
                        </select>
                        <input type="text" name="query" placeholder="<?=$lang['search']?>">
                        <input type="submit" value="<?=$lang['search']?>">
                    </form>
                    <div data-pressdo-toolbar-menu>
                        <a <?=$p_?> data-pressdo-history-mv>
                            <span ionicon ion-arrow-back></span> Prev
                        </a>
                        <a <?=$f_?> data-pressdo-history-mv>
                            Next <span ionicon ion-arrow-forward></span>
                        </a>
                    </div>
                    <ul data-pressdo-blockhistory>
                    <?php foreach ($data['history'] as $h) {
                        ?><li>
                            <time datetime="<?=date("Y-m-d\TH:i:s", $h['datetime']-32400)?>.000Z"><?=date("Y-m-d H:i:s", $h['datetime'])?></time>
                            <?php if($h['author'] === null){
                                $subject = 'div v="inline"><div v="inline-block"><a data-pressdo-user-ip>'.$h['author_ip'].'</a></div></div>';
                            }else{
                                $subject = '<a data-pressdo-user-member>'.$h['author'].'</a>';
                            } ?>
                            <?=str_replace(['@1@','@2@'], [$subject, $h['content']['ip'].$h['content']['member']], $lang['msg:b_user'])?>
                            <i><?php
                            switch($h['action']){
                                case 'aclgroup_add':
                                case 'aclgroup_remove':
                                    ?>(<?=str_replace('@1@','<b>'.$h['content']['aclgroup'].'</b>',$lang['msg:b_'.$h['action']])?>)<?php
                                    break;
                                case 'login_history':
                                case 'grant':
                                    ?>(<?=$lang['msg:b_'.$h['action']]?>)<?php
                                    break;
                            }
                            if($h['content']['duration'] < 0)
                                $l = $lang['msg:b_forever'];
                            elseif($h['content']['duration'] == null)
                                $l = null;
                            else{
                                $time = PressDo::formatTime($h['content']['duration']);
                                $timeSet = '';
                                foreach (array_keys($time) as $t){
                                    if($time[$t] != 0)
                                        $timeSet .= ' '.$time[$t].$lang['acl:'.$t];
                                }
                                $l = str_replace('@1@', $timeSet, $lang['msg:b_for']);
                            } ?>
                            </i>
                            #<?=$h['content']['id']?> <?=$l?>
                            <?php if($h['action'] !== 'login_history') { ?><span data-pressdo-history-gray data-pressdo-history-log><?=$h['content']['memo'].$h['content']['granted']?></span><?php } ?>
                        </li><?php
                    }?>
                    </ul>
                    <div data-pressdo-toolbar-menu>
                        <a <?=$p_?> data-pressdo-history-mv>
                            <span ionicon ion-arrow-back></span> Prev
                        </a>
                        <a <?=$f_?> data-pressdo-history-mv>
                            Next <span ionicon ion-arrow-forward></span>
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
        public static function License($args)
        {
            $data = $args[0];
            global $conf, $lang, $uri; ?>
            <head>
            <title> <?=$lang['License']?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['License']?></h1>
            </div>
            <div data-pressdo-doc-content>
                <div>
                    <h2>PressDo</h2>
                    <p>v<?=$data['version']?> (<?=$data['hash']?>) </p>
                    <p><?=$data['updated']?></p>
                    <p>Author: <a href="https://github.com/aaei924">PRASEOD-</a><br>
                    PressDo is licensed under the <a rel="license" href="https://github.com/PressDo/PressDoWiki/blob/dev/LICENSE" target="_blank">GNU Affero General Public License v3.0</a>.</p>
                    <h3>Contributors</h3>
                    <ul>
                        <li>admin@prws.kr (backend & frontend)</li>
                        <li>issac4892@prws.kr (readme)</li>
                    </ul>
                    <h3>Open source license</h3>
                    <ul>
                        <li>
                            <a href="https://github.com/HyungJu/readable-url" target="_blank">readable-url</a><br>
                            Author : <a href="https://github.com/HyungJu" target="_blank">Jude</a><br>
                            readable-url is licensed under the <a rel="license" href="https://github.com/HyungJu/readable-url/blob/master/LICENSE" target="_blank">MIT License</a>.
                        </li>
                        <li>
                            <a href="https://github.com/chrisboulton/php-diff" target="_blank">php-diff</a><br>
                            Author : <a href="https://github.com/chrisboulton" target="_blank">Chris Boulton</a><br>
                            php-diff is licensed under the <a rel="license" href="https://github.com/aaei924/php-diff-theseed/blob/master/LICENSE" target="_blank">BSD 3-Clause "New" or "Revised" License</a>.
                        </li>
                        <li>
                            <a href="https://github.com/PHPMailer/PHPMailer" target="_blank">PHPMailer</a><br>
                            Author : <a href="https://github.com/Synchro" target="_blank">Marcus Bointon</a><br>
                            PHPMailer is licensed under the <a rel="license" href="https://github.com/PHPMailer/PHPMailer/blob/master/LICENSE" target="_blank">GNU Lesser General Public License v2.1</a>.
                        </li>
                        <li>
                            <a href="https://github.com/erusev/parsedown" target="_blank">Parsedown</a><br>
                            Author : <a href="https://github.com/erusev" target="_blank">Emanuil Rusev</a><br>
                            Parsedown is licensed under the <a rel="license" href="https://github.com/PHPMailer/PHPMailer/blob/master/LICENSE" target="_blank">MIT License</a>.
                        </li>
                        <li>
                            <a href="https://github.com/jbowens/jBBCode" target="_blank">jBBCode</a><br>
                            Author : <a href="https://github.com/jbowens" target="_blank">Jackson Owens</a><br>
                            jBBCode is licensed under the <a rel="license" href="https://github.com/jbowens/jBBCode/blob/master/LICENSE.md" target="_blank">MIT License</a>.
                        </li>
                        <li>
                            <a href="https://github.com/mike42/wikitext" target="_blank">Wikitext</a><br>
                            Author : <a href="https://github.com/mike42" target="_blank">Michael Billington</a><br>
                            Wikitext is licensed under the <a rel="license" href="https://github.com/mike42/wikitext/blob/master/LICENSE.md" target="_blank">MIT License</a>.
                        </li>
                    </ul>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php
        }
        public static function pageList($args)
        {
            list($page,$data,$ns) = $args;
            global $conf, $lang, $uri, $_ns; ?>
            <head>
            <title> <?=$lang[$page]?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang[$page]?></h1>
            </div>
            <div data-pressdo-doc-content>
                <div><?php if($page == 'RandomPage'){ ?>
                    <fieldset data-pressdo-nsfield>
                        <form method="POST">
                            <div data-pressdo-pagelist>
                                <label data-pressdo-pagelist><?=$lang['namespace']?> :</label><select data-pressdo-pagelist name="namespace">
                                    <?php foreach ($ns as $n){
                                        if($n == $_GET['namespace'])
                                            $sel = 'selected';
                                        else
                                            $sel = '';
                                        ?><option value="<?=$n?>" <?=$sel?>><?=$n?></option><?php
                                    } ?>
                                    
                                </select>
                            </div>
                            <div data-pressdo-pagelist class="b">
                                <button type="submit" data-pressdo-button data-pressdo-button-blue><?=$lang['submit']?></button>
                            </div>
                        </form>
                    </fieldset>
                    <?php }else{ ?>
                        <p><?=$lang['msg:'.$page]?></p>
                    <?php }?>
                    <ul data-pressdo-ul>
                        <?php foreach($data as $d){
                            $txt = [
                                'OldPages' => '('.$lang['modifiedat'].':'.date('Y-m-d H:i:s', $d['datetime']).')',
                                'LongestPages' => '('.$d['length'].$lang['letters'].')',
                                'ShortestPages' => '('.$d['length'].$lang['letters'].')'
                            ];

                            if($conf['ForceShowNameSpace'] === false && $d['namespace'] == $_ns['document'])
                                $title = $d['title'];
                            else
                                $title = $d['namespace'].':'.$d['title'];
                            ?><li><a href="<?=$uri['wiki'].$title?>"><?=$title?></a> <?=$txt[$page]?></li><?php
                        } ?>
                    </ul>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php
        }
        public static function Upload($args)
        {
            $data = $args[0];
            global $conf, $lang, $uri, $_ns; ?>
            <head>
                <title> <?=$lang['FileUpload']?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['FileUpload']?></h1>
            </div>
            <div data-pressdo-doc-content>
                <div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" id="fileInput" ns="<?=$_ns['file']?>" accept="image/*" name="file" hidden="hidden">
                        <div class="g" data-pressdo-uploader>
                            <label data-pressdo-uploader for="fakeFileInput"><?=$lang['file_select']?></label>
                            <div class="ig">
                                <input data-pressdo-uploader id="fakeFileInput" type="text" readonly="readonly">
                                <span><button id="fubtn" type="button" data-pressdo-button>Select</button></span>
                            </div>
                        </div>
                        <div class="g" data-pressdo-uploader>
                            <label data-pressdo-uploader for="documentInput"><?=$lang['file_name']?></label>
                            <input data-pressdo-thread id="documentInput" type="text" name="document">
                        </div>
                        <textarea data-pressdo-uploader name="text" wrap="soft"><?=$conf['FileUploadText']?></textarea>
                        <div class="g" data-pressdo-uploader>
                            <label data-pressdo-uploader for="licenseSelect"><?=$lang['file_license']?></label>
                            <div dir="auto" dropdown-selector>
                                <div dropdown-toggle>
                                    <div dropdown-options>
                                        <input dropdown-input placeholder="<?=$lang['select']?>" id="licenseSelect" type="search" autocomplete="off">
                                    </div>
                                    <div dropdown-action>
                                        <button type="button" title="Clear Selection" v="none" dropdown-toggle-clear><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><path d="M6.895455 5l2.842897-2.842898c.348864-.348863.348864-.914488 0-1.263636L9.106534.261648c-.348864-.348864-.914489-.348864-1.263636 0L5 3.104545 2.157102.261648c-.348863-.348864-.914488-.348864-1.263636 0L.261648.893466c-.348864.348864-.348864.914489 0 1.263636L3.104545 5 .261648 7.842898c-.348864.348863-.348864.914488 0 1.263636l.631818.631818c.348864.348864.914773.348864 1.263636 0L5 6.895455l2.842898 2.842897c.348863.348864.914772.348864 1.263636 0l.631818-.631818c.348864-.348864.348864-.914489 0-1.263636L6.895455 5z"></path></svg></button>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="10" role="presentation" dropdown-toggle-open><path d="M9.211364 7.59931l4.48338-4.867229c.407008-.441854.407008-1.158247 0-1.60046l-.73712-.80023c-.407008-.441854-1.066904-.441854-1.474243 0L7 5.198617 2.51662.33139c-.407008-.441853-1.066904-.441853-1.474243 0l-.737121.80023c-.407008.441854-.407008 1.158248 0 1.600461l4.48338 4.867228L7 10l2.211364-2.40069z"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="g" data-pressdo-uploader>
                            <label data-pressdo-uploader for="categorySelect"><?=$lang['file_category']?></label>
                            <div dir="auto" dropdown-selector>
                                <div dropdown-toggle>
                                    <div dropdown-options>
                                        <input dropdown-input placeholder="<?=$lang['select']?>" id="categorySelect" type="search" autocomplete="off">
                                    </div>
                                    <div dropdown-action>
                                        <button type="button" title="Clear Selection" v="none" dropdown-toggle-clear><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><path d="M6.895455 5l2.842897-2.842898c.348864-.348863.348864-.914488 0-1.263636L9.106534.261648c-.348864-.348864-.914489-.348864-1.263636 0L5 3.104545 2.157102.261648c-.348863-.348864-.914488-.348864-1.263636 0L.261648.893466c-.348864.348864-.348864.914489 0 1.263636L3.104545 5 .261648 7.842898c-.348864.348863-.348864.914488 0 1.263636l.631818.631818c.348864.348864.914773.348864 1.263636 0L5 6.895455l2.842898 2.842897c.348863.348864.914772.348864 1.263636 0l.631818-.631818c.348864-.348864.348864-.914489 0-1.263636L6.895455 5z"></path></svg></button>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="10" role="presentation" dropdown-toggle-open><path d="M9.211364 7.59931l4.48338-4.867229c.407008-.441854.407008-1.158247 0-1.60046l-.73712-.80023c-.407008-.441854-1.066904-.441854-1.474243 0L7 5.198617 2.51662.33139c-.407008-.441853-1.066904-.441853-1.474243 0l-.737121.80023c-.407008.441854-.407008 1.158248 0 1.600461l4.48338 4.867228L7 10l2.211364-2.40069z"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div data-pressdo-editor-comment>
                            <label data-pressdo-editor-comment for="logInput"><?=$lang['editor:summary']?></label>
                            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
                        </div>
                        <span><?=stripslashes($conf['EditAgreeText'])?></span><?php
                        if (!$_SESSION['member']['username']) { ?>
                            <br><p data-pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_edit'])?></p><?php
                        } ?>
                        <button  data-pressdo-button-wideright data-pressdo-button-editor data-pressdo-button-blue data-pressdo-button data-pressdo-button-editor-submit type="button"><?=$lang['Upload']?></button>
                    </form>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php
        }
    }
}
