<?php
namespace PressDo {
    include 'config.php';
    require_once 'PressDoLib.php';
    class WikiSkin {
        public static function header($DocNm) {
            global $conf;
            include './skin/default/html/head.html';
            ?><title> <?= $DocNm ?> - <?=$conf['Name'] ?> </title> <?php
        }

        public static function main($Doc, $array, $action = 'view')
        {
            $DocContent = $array['content'];
            global $conf; ?>
            <style>
                .wiki-fn, .fn a{
                    /* 주석 밑줄 제거 */
                    text-decoration:none;
                }
            </style>
            <script>
                // 버튼
                var docMenu = document.createElement('form');
                docMenu.method = 'GET';
                var from = document.createElement('input');
                from.setAttribute('type', 'hidden');
                from.setAttribute('name', 'title');
                from.setAttribute('value', '<?=$Doc?>');
                function goMenu(page) {
                    docMenu.appendChild(from);
                    document.body.appendChild(docMenu);
                    docMenu.action = page + '.php';
                    docMenu.submit();
                }
            </script><div align="center" style="text-align:left;"><?php
            include './skin/default/html/main.html';
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
        <footer>
            <hr>
            <p> ⓒCopyright <?=$conf['CopyRight']?></p>
            <p> <?=$conf['HelpMail']?> | <?=$conf['TermsOfUse']?> | <?=$conf['SecPolicy']?> </p>
            <br>
            <br>
            <br>
            <br>
        </footer><?php
        }

        public static function FullPage($DocNm, $DocContent, $action = 'view')
        {
            WikiSkin::header($DocNm);
            WikiSkin::main($DocNm, $DocContent, $action);
        }
    }
}
?>
