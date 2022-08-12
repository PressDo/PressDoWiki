<?php

use Latte\Runtime as LR;

/** source: /home/pi/phs/pressdo/views/layouts/delete.latte */
final class Template3cca544ded extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<head>
    <title> <?=$_GET[\'title\']?> (<?=$lang[\'delete\']?>) - <?=$conf[\'SiteName\']?> </title>
</head>
<div data-pressdo-content-header>
    <h1 data-pressdo-doc-title><a href="<?=$uri[\'wiki\'].$Doc?>"><span data-pressdo-title-namespace><?=$NS?></span><?=$Title?></a> 
        <small data-pressdo-doc-action>(<?=$lang[\'delete\']?>)</small>
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
        <div data-pressdo-loginform class="c comment">
            <label class="comment" for="logInput"><?=$lang[\'editor:summary\']?></label>
            <input data-pressdo-edit-summary type="text" name="summary" id="logInput">
        </div>
        <label data-pressdo-pagelist><input type="checkbox" name="agree" id="agree"> <?=$lang[\'msg:ThisIsNotMove\']?></label>
        <p data-pressdo-delete><b><?=str_replace(\'@1@\', $uri[\'move\'].$Doc, $lang[\'msg:PleaseDontMove\'])?></b></p><?php
        if (!$_SESSION[\'member\'][\'username\']) { ?>
            <p data-pressdo-delete data-pressdo-warning-unlogined><?=str_replace(\'@1@\', PressDo::getip(), $lang[\'msg:unlogined_edit\'])?></p><?php
        } ?>
        <div class="btn-area">
            <button class="btn btn-red" editor-msg="<?=$lang[\'msg:CheckDelMsg\']?>" type="button" id="delb"><?=$lang[\'delete\']?></button>
        </div>
    </form>
</div>';
		return get_defined_vars();
	}

}
