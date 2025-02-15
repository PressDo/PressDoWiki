<?php
namespace PressDo\app\Controllers\Pages\api;

use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Namespaces,Database};

class Preview extends Controller
{
    public function makeData(): never
    {
        $content = self::readSyntax($_POST['text'], [
            'title' => $_POST['title'],
            'namespace' => Namespaces::all(),
            'thread' => false
        ]);
        echo '<link rel="stylesheet" href="/src/style/document.css">
            <script defer src="/src/script/document.js"></script>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.11.1/dist/katex.min.css" integrity="sha384-zB1R0rpPzHqg7Kpt0Aljp8JPLqbXI3bhnPWROx27a9N0Ll6ZP/+DiW/UqRcLbRjq" crossorigin="anonymous"/>
            <script defer src="https://cdn.jsdelivr.net/npm/katex@0.11.1/dist/katex.min.js" integrity="sha384-y23I5Q6l+B6vatafAwxRu/0oK/79VlbSz7Q9aiSZUvyWYIYsd+qj+o24G5ZU2zJz" crossorigin="anonymous"></script>
            <script defer src="https://cdn.jsdelivr.net/npm/katex@0.11.1/dist/contrib/auto-render.min.js" integrity="sha384-kWPLUVMOks5AQFrykwIup5lo0m3iMkkHrD0uJ4H5cjeGihAutqP0yW0J6dpFiVkI" crossorigin="anonymous" onload="renderMathInElement(document.body);"></script>';
        echo $content['html'];
        exit;
    }
}