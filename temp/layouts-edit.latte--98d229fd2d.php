<?php

use Latte\Runtime as LR;

/** source: skin/layouts/edit.latte */
final class Template98d229fd2d extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<head>
    <script>
        DocContent = <?=json_encode([\'c\' => $raw], JSON_UNESCAPED_UNICODE)?>;
        viewerURI = "<?=$uri[\'wiki\']?>";
        monacoVersion = "<?=$conf[\'editor_version\']?>";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/<?=$conf[\'editor_version\']?>/min/vs/loader.js"></script>
    <script src="/src/script/monaco.js"></script>
    <title> <?=$_GET[\'title\']?> (<?=$lang[\'edit\']?>) - <?=$conf[\'SiteName\']?> </title>
</head>
<div data-pressdo-content-header>
    <?=self::Button([\'backlink\', \'delete\', \'move\'],$Doc)?>
    <h1 data-pressdo-doc-title><a href="<?=$uri[\'wiki\'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a> 
        <small data-pressdo-doc-action><?php echo (!$ver)? \'(\'.$lang[\'editor:create\'].\')\':\'(\'.str_replace(\'@1@\', $ver, $lang[\'editor:modify\']).\')\'; ?></small>
    </h1>
</div>
<div class="wiki-content" >
    <?php if($err){ ?>
    <div class="a e">
        <strong><?=$lang[\'msg:error\']?></strong>
        <span><?=$err?></span>
    </div>
    <?php } ?>
    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?=$token?>">
        <ul class="editor top">
            <li class="editor top">
                <button id="m" class="a e tr editor top" type="button" class="a"><?=$lang[\'editor:monaco\']?></button>
            </li>
            <li class="editor top">
                <button id="r" class="e editor top" type="button"><?=$lang[\'editor:raw\']?></button>
            </li>
            <li class="editor top">
                <button id="p" class="e editor top" type="button"><?=$lang[\'editor:preview\']?></button>
            </li>
            <li class="t editor top">
                <div class="editor top">
                    <button id="btn-bold" class="editor top" type="button"><?=$lang[\'editor:bold\']?></button>
                </div><div class="editor top">
                    <button id="btn-italic" class="editor top" type="button"><?=$lang[\'editor:italic\']?></button>
                </div><div class="editor top">
                    <button id="btn-strike" class="editor top" type="button"><?=$lang[\'editor:strike\']?></button>
                </div><div class="editor top">
                    <button id="btn-link" class="editor top" type="button"><?=$lang[\'editor:link\']?></button>
                </div><div class="editor top">
                    <button id="btn-file" class="editor top" type="button"><?=$lang[\'editor:file\']?></button>
                </div><div class="editor top">
                    <button id="btn-ref" class="editor top" type="button"><?=$lang[\'editor:ref\']?></buttonv>
                </div><div class="editor top">
                    <button id="btn-template"n class="editor top" type="button"><?=$lang[\'editor:template\']?></button>
                </div>
            </li>
        </ul>
        <div class="c editor">
            <div id="m" class="a editor">
                <div id="monaco" class="editor showUnused"></div>
            </div><div id="r" class="editor">
                <textarea class="editor" id="pressdo-anchor" name="content"><?=$raw?></textarea>
            </div><div id="p" class="p editor"></div>
        </div>
        <div class="g comment">
            <label class="comment" for="logInput"><?=$lang[\'editor:summary\']?></label>
            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
        </div>
        <label><input type="checkbox" name="agree" id="agree"> <span><?=stripslashes($conf[\'EditAgreeText\'])?></span></label><?php
        if (!$_SESSION[\'member\'][\'username\']) { ?>
            <p data-pressdo-warning-unlogined><?=str_replace(\'@1@\', PressDo::getip(), $lang[\'msg:unlogined_edit\'])?></p><?php
        } ?>
        <div class="btn-area">
            <button class="btn-blue btn btn-wideright" type="button" id="ef" editor-uri="<?=$uri[\'edit\'].rawurlencode($Doc)?>" editor-msg="<?=$lang[\'msg:please_agree\']?>">저장</button>
        </div>
    </form>
</div>
<footer data-pressdo-con-footer>';
		return get_defined_vars();
	}

}
