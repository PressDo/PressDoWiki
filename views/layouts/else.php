<?php
namespace PressDo
{
    class SkinTemplate
    {
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
            <div class="wiki-content">
            <?php if($err){ 
                if(is_array($err))
                    $err = str_replace(['@1@','@2@'], [$err[1], $err[2]], $lang['msg:'.$err[0]]);
                else
                    $err = $lang['msg:'.$err];
                ?>
                <div class="a e">
                    <strong><?=$lang['msg:error']?></strong>
                    <span><?=$err?></span>
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
                    <div class="a">
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
                        <button type="submit" class="btn btn-wideright btn-blue"><?=$lang['submit']?></button>
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
            <div class="wiki-content">
            <?php if($err){ 
                if(is_array($err))
                    $err = str_replace(['@1@','@2@'], [$err[1], $err[2]], $lang['msg:'.$err[0]]);
                else
                    $err = $lang['msg:'.$err];
                ?>
                <div class="a e">
                    <strong><?=$lang['msg:error']?></strong>
                    <span><?=$err?></span>
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
                                                            <button data-pressdo-thread-button class="btn"></button>
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
                            <button class="btn" type="submit"><?=$lang['change']?></button>
                        </form>
                    <?php }
                    if($dat['updateThreadDocument'] === true){ ?>
                        <form method="POST">
                            [ADMIN] <?=$lang['updateThreadDocument']?>
                            <input type="text" name="document" value="<?=$ns.$title?>">
                            <button class="btn" type="submit"><?=$lang['change']?></button>
                        </form>
                    <?php }
                    if($dat['updateThreadTopic'] === true){ ?>
                        <form method="POST">
                            [ADMIN] <?=$lang['updateThreadTopic']?>
                            <input type="text" name="topic" value="<?=$dat['topic']?>">
                            <button class="btn" type="submit"><?=$lang['change']?></button>
                        </form>
                    <?php } ?>
                    <form method="POST">
                        <div data-pressdo-loginform class="c">
                            <textarea data-pressdo-thread id="contentInput" rows="5" <?=($dat['status']!='normal'?' disabled="disabled"':'name="text"')?> required><?=($dat['status']!='normal'?$lang['msg:thread_status_'.$dat['status']]:'')?></textarea>
                            <?php if(!$_SESSION['member']['username']){ ?>
                            <p data-pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_comment'])?></p><?php } ?>
                        </div>
                        <button type="submit" <?=($dat['status']!='normal'?' disabled="disabled"':'')?> class="btn btn-wideright btn-blue"><?=$lang['submit']?></button>
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
                                <button id="bt-wiki" v="block" class="btn" type="button"><?=$lang['view_original']?></button>
                                <button id="bt-origin" v="none" class="btn" type="button"><?=$lang['view_wiki']?></button>
                                <button class="btn btn-red" type="button">[ADMIN] <?=$lang['hide_thread_comment']?></button>
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
            $p_ = (!$data['prev_page'])?'data-pressdo-history-null' : 'href='.$uri['BlockHistory'].$uri['prefix'].'until='.$data['prev_page'];
            $f_ = (!$data['next_page'])?'data-pressdo-history-null' : 'href='.$uri['BlockHistory'].$uri['prefix'].'from='.$data['next_page']; ?>
            <head><title><?=$lang['BlockHistory']?> - <?=$conf['SiteName']?></title></head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['BlockHistory']?></h1>
            </div>
            <div class="wiki-content">
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
                    <div class="bt-g">
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
                                $time = PressDo::formatTime(intval($h['content']['duration']));
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
                    <div class="bt-g">
                        <a <?=$p_?> data-pressdo-history-mv>
                            <span ionicon ion-arrow-back></span> Prev
                        </a>
                        <a <?=$f_?> data-pressdo-history-mv>
                            Next <span ionicon ion-arrow-forward></span>
                        </a>
                    </div>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php
        }
        public static function License($args)
        {
            $data = $args[0];
            global $conf, $lang; ?>
            <head>
            <title> <?=$lang['License']?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['License']?></h1>
            </div>
            <div class="wiki-content">
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
            <div class="wiki-content">
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
                                <button type="submit" class="btn btn-blue"><?=$lang['submit']?></button>
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
            list($Licenses, $Categories) = $args;
            global $conf, $lang, $uri, $_ns; ?>
            <head>
                <title> <?=$lang['FileUpload']?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['FileUpload']?></h1>
            </div>
            <div class="wiki-content">
                <div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" id="fileInput" ns="<?=$_ns['file']?>" accept="image/*" name="file" hidden="hidden">
                        <div class="g" data-pressdo-uploader>
                            <label data-pressdo-uploader for="fakeFileInput"><?=$lang['file_select']?></label>
                            <div class="ig">
                                <input data-pressdo-uploader id="fakeFileInput" type="text" readonly="readonly">
                                <span><button id="fubtn" type="button" class="btn">Select</button></span>
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
                                <div dropdown-toggle="arrow_l">
                                    <div dropdown-options>
                                    <span dropdown-selected="l"></span>
                                        <input dropdown-input="arrow_l" placeholder="<?=$lang['select']?>" id="licenseSelect" type="search" autocomplete="off">
                                    </div>
                                    <div dropdown-action="arrow_l">
                                        <button type="button" title="Clear Selection" v="none" dropdown-toggle-clear><svg dropdown-arrow xmlns="http://www.w3.org/2000/svg" width="10" height="10"><path d="M6.895455 5l2.842897-2.842898c.348864-.348863.348864-.914488 0-1.263636L9.106534.261648c-.348864-.348864-.914489-.348864-1.263636 0L5 3.104545 2.157102.261648c-.348863-.348864-.914488-.348864-1.263636 0L.261648.893466c-.348864.348864-.348864.914489 0 1.263636L3.104545 5 .261648 7.842898c-.348864.348863-.348864.914488 0 1.263636l.631818.631818c.348864.348864.914773.348864 1.263636 0L5 6.895455l2.842898 2.842897c.348863.348864.914772.348864 1.263636 0l.631818-.631818c.348864-.348864.348864-.914489 0-1.263636L6.895455 5z"></path></svg></button>
                                        <svg id="arrow_l" xmlns="http://www.w3.org/2000/svg" width="14" height="10" role="presentation" dropdown-toggle-open><path d="M9.211364 7.59931l4.48338-4.867229c.407008-.441854.407008-1.158247 0-1.60046l-.73712-.80023c-.407008-.441854-1.066904-.441854-1.474243 0L7 5.198617 2.51662.33139c-.407008-.441853-1.066904-.441853-1.474243 0l-.737121.80023c-.407008.441854-.407008 1.158248 0 1.600461l4.48338 4.867228L7 10l2.211364-2.40069z"></path></svg>
                                    </div>
                                </div>
                                <ul dropdown-name="arrow_l" role="listbox" data-pressdo-toc-fold=hide id="list-dropdown-menu">
                                    <?php foreach($Licenses as $c){
                                        ?>
                                        <li role="option" dropdown-option="l"><?=$c['name']?></li><?php
                                    } ?>
                                </ul>
                            </div>
                        </div>
                        <p data-pressdo-uploader><?=$conf['FileUploadWarning']?></p>
                        <div class="g" data-pressdo-uploader>
                            <label data-pressdo-uploader for="categorySelect"><?=$lang['file_category']?></label>
                            <div dir="auto" dropdown-selector>
                                <div dropdown-toggle="arrow_c">
                                    <div dropdown-options>
                                        <span dropdown-selected="c"></span>
                                        <input dropdown-input="arrow_c" placeholder="<?=$lang['select']?>" id="categorySelect" type="search" autocomplete="off">
                                    </div>
                                    <div dropdown-action="arrow_c">
                                        <button type="button" title="Clear Selection" v="none" dropdown-toggle-clear><svg dropdown-arrow xmlns="http://www.w3.org/2000/svg" width="10" height="10"><path d="M6.895455 5l2.842897-2.842898c.348864-.348863.348864-.914488 0-1.263636L9.106534.261648c-.348864-.348864-.914489-.348864-1.263636 0L5 3.104545 2.157102.261648c-.348863-.348864-.914488-.348864-1.263636 0L.261648.893466c-.348864.348864-.348864.914489 0 1.263636L3.104545 5 .261648 7.842898c-.348864.348863-.348864.914488 0 1.263636l.631818.631818c.348864.348864.914773.348864 1.263636 0L5 6.895455l2.842898 2.842897c.348863.348864.914772.348864 1.263636 0l.631818-.631818c.348864-.348864.348864-.914489 0-1.263636L6.895455 5z"></path></svg></button>
                                        <svg id="arrow_c" xmlns="http://www.w3.org/2000/svg" width="14" height="10" role="presentation" dropdown-toggle-open><path d="M9.211364 7.59931l4.48338-4.867229c.407008-.441854.407008-1.158247 0-1.60046l-.73712-.80023c-.407008-.441854-1.066904-.441854-1.474243 0L7 5.198617 2.51662.33139c-.407008-.441853-1.066904-.441853-1.474243 0l-.737121.80023c-.407008.441854-.407008 1.158248 0 1.600461l4.48338 4.867228L7 10l2.211364-2.40069z"></path></svg>
                                    </div>
                                </div>
                                <ul dropdown-name="arrow_c" role="listbox" data-pressdo-toc-fold=hide id="list-dropdown-menu">
                                    <?php foreach($Categories as $c){
                                        ?>
                                        <li role="option" dropdown-option="c"><?=$c['name']?></li><?php
                                    } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="comment">
                            <label class="comment" for="logInput"><?=$lang['editor:summary']?></label>
                            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
                        </div>
                        <span><?=stripslashes($conf['EditAgreeText'])?></span><?php
                        if (!$_SESSION['member']['username']) { ?>
                            <br><p data-pressdo-warning-unlogined><?=str_replace('@1@', PressDo::getip(), $lang['msg:unlogined_edit'])?></p><?php
                        } ?>
                        <button class="editor btn-wideright btn-blue btn" type="button"><?=$lang['Upload']?></button>
                    </form>
                </div>
            </div>
            <footer data-pressdo-con-footer><?php
        }
        public static function Search($args)
        {
            global $lang, $conf, $_ns, $uri;
            list($keyword) = $args;
            ?>
            <head>
                <title> <?=$lang['search']?> - <?=$conf['SiteName']?> </title>
            </head>
            <div data-pressdo-content-header>
                <h1 data-pressdo-doc-title><?=$lang['search']?></h1>
            </div>
            <div class="wiki-content">
                <div>
                    <form class="spacing-ud" method="GET">
                        <input type="hidden" name="page" value="Search">
                        <select class="search" name="namespace">
                            <?php
                            foreach(array_keys($_ns) as $n){
                                ?><option value="<?=$_ns[$n]?>"><?=$_ns[$n]?></option><?php
                            }
                            ?>
                        </select>
                        <select class="search" name="target">
                            <option value="title_content"><?=$lang['search:title_content']?></option>
                            <option value="title"><?=$lang['search:title']?></option>
                            <option value="content"><?=$lang['search:content']?></option>
                            <option value="raw"><?=$lang['search:raw']?></option>
                        </select>
                        <input class="search" type="text" name="query">
                        <button type="submit" class="btn btn-blue"><?=$lang['search']?></button>
                    </form>
                    <div class="a search">
                        <div class="l">
                            <i class="ion-ios-arrow-forward"></i><?=$lang['msg:search_go_directly']?>
                        </div>
                        <div class="r">
                            <a class="bt-white" href="<?=$uri['wiki'].$keyword?>"><?=str_replace('@1@', "'$keyword'", $lang['search:go'])?></a>
                        </div>
                    </div>
                    <div class="s">
                        <?=str_replace('@1@', $resultCount, $lang['search:count'])?> / <?=str_replace('@1@', $period, $lang['search:period'])?>
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
                                $time = PressDo::formatTime(intval($h['content']['duration']));
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
                </div>
            </div>
            <footer data-pressdo-con-footer><?php
        }
    }
}
