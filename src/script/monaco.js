(()=>{
    namumark_register = function (monaco) {
        monaco.languages.register({
            id: 'namumark',
            aliases: [
                'namumark',
                'Namumark',
                'NamuMark'
            ]
        });
        monaco.languages.setLanguageConfiguration('namumark', {
            comments: {
                lineComment: '##'
            },
            brackets: [
                ['{{{', '}}}'],
                ['[', ']'],
                ['(', ')'],
            ],
            surroundingPairs: [
                ['{', '}'], ['[', ']'], ['(', ')'],
                ['\'', '\''], ['~', '~'], ['-', '-'],
                ['_', '_'], ['^', '^'], [',', ','],
            ],
            autoClosingPairs: [
                {open: '{', close: '}'},{open: '[', close: ']'},{open: '(', close: ')'},
                {open: '~~', close: '~~'}, {open: '--', close: '--'}, {open: '__', close: '__'}, {open: '^^', close: '^^'},{open: ',,', close: ',,'},
            ],
            wordPattern: /(-?\d.\d\w)|([^`~!@#%^&*()-=+\[{\]}\|;:'",.<>/?\s]+)/g,
        });
        monaco.languages.registerLinkProvider('namumark', {
            provideLinks:function(TextModel, _) {
                let ResolvedLinks = [];
                let LineIndex, LineCount;
    
                LineCount = TextModel.getLineCount();
                for(LineIndex = 1;LineIndex <= LineCount;LineIndex++) {
                    let LineContent = TextModel.getLineContent(LineIndex);
                    let LineWikiRegExp = /(\[\[)((?:\\.|[^\]\|])+)|(\[(include|youtube|nicovideo|kakaotv)\()((?:\\.|[^,])+?)(?:,.*?)?\)\]/g;
                    let URIRegExp = /(\w+)\:\/\/(?:www\.)?([^\s\|\]\'\"]+)/g;
                    let LineWiki;
                    while(null != (LineWiki = LineWikiRegExp.exec(LineContent))) {
                        let range, tooltip, url;
                        let LineURI;
                        let VideoURIFormatter = (ServiceId, VideoId) => {
                            switch(ServiceId) {
                                case 'youtube':
                                    return `https://www.youtube.com/watch?v=${VideoId}`;
                                    break;
                                case 'kakaotv':
                                    return `https://tv.kakao.com/v/${VideoId}`;
                                    break;
                                case 'nicovideo':
                                    return `https://www.nicovideo.jp/watch/${VideoId}`;
                                    break;
                                case 'navertv':
                                    return `https://tv.naver.com/v/${VideoId}`;
                                    break;
                                case 'vimeo':
                                    return `https://vimeo.com/${VideoId}`;
                                    break;
                                default:
                                    console.warn(`VideoURIFormatter: Undefined ServiceId "${ServiceId}"`);
                                    return;
                            }
                        };
    
                        switch(LineWiki[4]) {
                            case 'youtube':
                            case 'kakaotv':
                            case 'nicovideo':
                            case 'navertv':
                                LineWiki[0] = LineWiki[3] + LineWiki[5];
                                range = new monaco.Range(
                                    LineIndex,
                                    LineWiki.index+1+LineWiki[3].length,
                                    LineIndex,
                                    LineWiki.index+1+LineWiki[0].length
                                );
                                tooltip = `${LineWiki[4]}:${LineWiki[5]}`;
                                url = VideoURIFormatter(LineWiki[4], LineWiki[5]);
                                break;
                            case 'include':
                                /* 재정렬 */
                                LineWiki[0] = LineWiki[3] + LineWiki[5];
                                LineWiki[1] = LineWiki[1] || LineWiki[3];
                                LineWiki[2] = LineWiki[2] || LineWiki[5];
                                LineWiki[3] = LineWiki[4] || null;
                                LineWiki[4] = LineWiki[5] = null;
                            default:
                                if(LineURI = URIRegExp.exec(LineWiki[2])) {
                                    range = new monaco.Range(
                                        LineIndex,
                                        LineWiki.index+1+LineWiki[1].length,
                                        LineIndex,
                                        LineWiki.index+1+LineWiki[0].length
                                    );
                                    tooltip = LineURI[2];
                                    url = LineURI[0];
                                }
                                else {
                                    if(LineWiki[2].length>1 && LineWiki[2].match(/^:(파일|분류):/)) {
                                        let WikiName = LineWiki[2].substr(1);
                                        range = new monaco.Range(
                                            LineIndex,
                                            LineWiki.index+1+LineWiki[1].length+1,
                                            LineIndex,
                                            LineWiki.index+1+LineWiki[0].length
                                        );
                                        tooltip = WikiName;
                                        url = window.location.protocol + '//' + window.location.host + "/w/" + encodeURIComponent(WikiName);
                                    }
                                    else {
                                        let WikiName = LineWiki[2].replace(/\\(.)/, '$1');
                                        range = new monaco.Range(
                                            LineIndex,
                                            LineWiki.index+1+LineWiki[1].length,
                                            LineIndex,
                                            LineWiki.index+1+LineWiki[0].length
                                        );
                                        tooltip = WikiName;
                                        url = window.location.protocol + '//' + window.location.host + "/w/" + encodeURIComponent(WikiName);
                                    }
                                }
                        }
    
                        ResolvedLinks.push({
                            range: range,
                            tooltip: tooltip,
                            url: url
                        });
                    }
                }
                return {
                    links: ResolvedLinks,
                    dispose:()=>{ }
                };
            }
        });
        monaco.languages.setMonarchTokensProvider('namumark', {
            defaultToken: '',
            tokenPostfix: '.namumark',
            escapes: /\\./,
            tokenizer: {
                root: [
                    /* escapes */
                    [/@escapes/, 'string.escape'],
    
                    /* 문단 */
                    [/^(={1,6})(#?)(\s.+\s)(#?)(\1)(\s*)$/, 'keyword'],
    
                    /* 주석 */
                    [/##.*/, 'comment'],
    
                    /* 인용문 */
                    [/^\s*>+/, 'comment'],
    
                    /* 수평줄 */
                    [/^\s*-{4,9}\s*$/, 'meta.separator'],
    
                    /* 링크 */
                    [/\[{2}/, {token: 'delimiter', bracket: '@open', next: '@link'}],
                    [/\]{2}/, {token: 'delimiter', bracket: '@close'}],
    
                    /* 각주 */
                    [/(\[)(\*)/, ['delimiter', {token: 'comment', bracket: '@open', next: '@reference'}]],
    
                    /* 매크로 */
                    [/\[/, {token: 'delimiter', bracket: '@open', next: '@macro'}],
                    [/\]/, {token: 'delimiter', bracket: '@close'}],
    
                    /* code */
                    [/(\{{3})(\#\!)(\w+)/, {
                        cases: {
                            '$3==syntax': ['keyword', 'delimiter', {token: 'attribute.value', next: '@codeSyntax.$3', bracket: '@open'}],
                            '$3==html': ['keyword', 'delimiter', {token: 'attribute.value', next: '@codeWithType.$3', nextEmbedded: 'html', bracket: '@open'}],
                            '$3==latex': ['keyword', 'delimiter', {token: 'attribute.value', next: '@codeWithType.$3', nextEmbedded: 'latex', bracket: '@open'}],
                            '$3==wiki': ['keyword', 'delimiter', {token: 'attribute.value', next: '@codeWikiAttributes', bracket: '@open'}],
                            '$3==folding': ['keyword', 'delimiter', {token: 'attribute.value', next: '@codeWikiAttributes', bracket: '@open'}],
                            '@default': ['keyword', 'white', {token: 'white', next: '@code', bracket: '@open'}],
                        }
                    }],
                    [/(\{{3})(\+|\-)([0-9]+)/, ['keyword', 'delimiter', {token: 'attribute.value', next: '@codeWiki', bracket: '@open'}]],
                    [/(\{{3})(#)(aliceblue|antiquewhite|aqua|aquamarine|azure|beige|bisque|black|blanchedalmond|blue|blueviolet|brown|burlywood|cadetblue|chartreuse|chocolate|coral|cornflowerblue|cornsilk|crimson|cyan|darkblue|darkcyan|darkgoldenrod|darkgray|darkgreen|darkgrey|darkkhaki|darkmagenta|darkolivegreen|darkorange|darkorchid|darkred|darksalmon|darkseagreen|darkslateblue|darkslategray|darkslategrey|darkturquoise|darkviolet|deeppink|deepskyblue|dimgray|dimgrey|dodgerblue|firebrick|floralwhite|forestgreen|fuchsia|gainsboro|ghostwhite|gold|goldenrod|gray|green|greenyellow|grey|honeydew|hotpink|indianred|indigo|ivory|khaki|lavender|lavenderblush|lawngreen|lemonchiffon|lightblue|lightcoral|lightcyan|lightgoldenrodyellow|lightgray|lightgreen|lightgrey|lightpink|lightsalmon|lightseagreen|lightskyblue|lightslategray|lightslategrey|lightsteelblue|lightyellow|lime|limegreen|linen|magenta|maroon|mediumaquamarine|mediumblue|mediumorchid|mediumpurple|mediumseagreen|mediumslateblue|mediumspringgreen|mediumturquoise|mediumvioletred|midnightblue|mintcream|mistyrose|moccasin|navajowhite|navy|oldlace|olive|olivedrab|orange|orangered|orchid|palegoldenrod|palegreen|paleturquoise|palevioletred|papayawhip|peachpuff|peru|pink|plum|powderblue|purple|rebeccapurple|red|rosybrown|royalblue|saddlebrown|salmon|sandybrown|seagreen|seashell|sienna|silver|skyblue|slateblue|slategray|slategrey|snow|springgreen|steelblue|tan|teal|thistle|tomato|turquoise|violet|wheat|white|whitesmoke|yellow|yellowgreen|[0-9a-f]{3}|[0-9a-f]{6})(\s)/, ['keyword', 'attribute.value', {token: 'attribute.value', next: '@codeWiki.$3', bracket: '@open'}, 'white']],
                    [/\{{3}/, {token: 'keyword', next: '@code', bracket: '@open'}],
                    [/\}{3}/, {token: 'keyword', bracket: '@close'}],
    
                    /* 기타 텍스트 속성 */
                    [/\'{3}/, {
                        cases: {
                            '$S2==strong': {token: 'strong', next: '@pop', bracket: '@close'},
                            '@default': {token: 'strong', next: '@root.strong', bracket: '@open'},
                        }
                    }],
                    [/\'{2}/, {
                        cases: {
                            '$S2==emphasis': {token: 'emphasis', next: '@pop', bracket: '@close'},
                            '@default': {token: 'emphasis', next: '@root.emphasis', bracket: '@open'},
                        }
                    }],
                    [/.$/, {
                        cases: {
                            '$S2==strong': {token: '$S2', next: '@pop', bracket: '@close'},
                            '$S2==emphasis': {token: '$S2', next: '@pop', bracket: '@close'},
                            '@default': {token: 'white'},
                        }
                    }],
                    [/./, {token:'$S2'}],
                ],
                reference: [
                    [/\s+/, {token: 'white', next: '@referenceContent'}],
                    [/\]/, {token: 'delimiter', next: '@pop', bracket: '@close'}],
                    [/./, 'attribute.value'],
                ],
                referenceContent: [
                    [/\]/, {token: '@rematch', next: '@pop', bracket: '@close'}],
                    {include: '@root'}
                ],
                macro: [
                    [/@escapes/, 'string.escape'],
                    [/\]/, {token: 'delimiter', next: '@pop', bracket: '@close'}],
                    [/\(/, {token: 'delimiter', next: '@macroArguments', bracket: '@open'}],
                    [/\)/, {token: 'delimiter', bracket: '@close'}],
                    [/math/, {token: 'tag', next: '@macroArgumentWithType.latex'}],
                    [/date|br|include|목차|tableofcontents|각주|footnote|pagecount|age|dday|ruby|anchor|math|youtube|kakaotv|nicovideo/i, 'tag'],
                    [/./, 'invalid'],
                ],
                macroArgumentWithType: [
                    [/\(/, {token: 'delimiter', next: '@macroEmbedded', nextEmbedded: '$S2', bracket: '@open'}],
                    [/\)\]/, {token: '@rematch', next: '@pop', bracket: '@close'}],
                ],
                macroEmbedded: [
                    [/\)\]/, {token: '@rematch', next: '@pop', nextEmbedded: '@pop', bracket: '@close'}],
                ],
                macroArguments: [
                    [/@escapes/, 'string.escape'],
                    [/\)\]/, {token: '@rematch', next: '@pop', bracket: '@close'}],
                    [/=/, {token: 'delimiter', next: '@macroArgumentsItem'}],
                    [/,/, {token: 'delimiter'}],
                    [/./, 'attribute.name'],
                ],
                macroArgumentsItem: [
                    [/@escapes/, 'string.escape'],
                    [/\)/, {token: '@rematch', next: '@pop'}],
                    [/,/, {token: 'delimiter', next: '@pop'}],
                    [/./, 'attribute.value'],
                ],
                link: [
                    [/@escapes/, 'string.escape'],
                    [/\]{2}/, {token: '@rematch', next: '@pop', bracket: '@close'}],
                    [/\|/, {token: 'delimiter', next: '@linkText'}],
                    [/[^\\\|\]]+/, 'string.link'],
                ],
                linkText: [
                    [/\[{2}/, {token: 'delimiter', bracket: '@open', next: '@link'}],
                    [/\]{2}/, {token: '@rematch', next: '@pop', bracket: '@close'}],
                    {include: '@root'}
                ],
                code: [
                    [/\{{3}/, {token: 'white', next: '@codeInDepth', bracket: '@open'}],
                    [/\}{3}/, {token: '@rematch', next: '@pop', bracket: '@close'}],
                ],
                codeInDepth: [
                    [/\{{3}/, {token: 'white', next: '@codeInDepth', bracket: '@open'}],
                    [/\}{3}/, {token: 'white', next: '@pop', bracket: '@close'}],
                ],
                codeSyntax: [
                    [/\s+(\w+)/, {
                        cases: {
                            '@default': {token: 'attribute.value', next: '@codeWithType.$1', nextEmbedded: '$1'}
                        }
                    }],
                    [/\}{3}/, {token: '@rematch', next: '@pop'}],
                ],
                codeWikiAttributes: [
                    [/\}{3}/, {token: '@rematch', next: '@pop'}],
                    [/(\w+)(\=)(\")([^\"]*?|@escapes)(\")(\s*)$/, ['attribute.name', 'white', 'white', 'attribute.value', {token: 'white', next: '@codeWiki'}, 'white']],
                    [/(\w+)(\=)(\')([^\']*?|@escapes)(\')(\s*)$/, ['attribute.name', 'white', 'white', 'attribute.value', {token: 'white', next: '@codeWiki'}, 'white']],
                    [/(\w+)(\=)(\")([^\"]*?|@escapes)(\")/, ['attribute.name', 'white', 'white', 'attribute.value', 'white']],
                    [/(\w+)(\=)(\')([^\']*?|@escapes)(\')/, ['attribute.name', 'white', 'white', 'attribute.value', 'white']],
                    [/.$/, {token: 'invalid', next: '@codeWiki'}],
                    [/./, 'invalid'],
                ],
                codeWiki: [
                    [/\}{3}/, {token: '@rematch', next: '@pop'}],
                    {include: '@root'}
                ],
                codeWithType: [
                    [/\}{3}/, {token: '@rematch', next: '@pop', nextEmbedded: '@pop'}],
                ]
            }
        });
    }

    //import namumark_register from './monaco-namumark.js'
    let domready = (callback) => {
        if(document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    };

    domready(()=>{
        let target = document.getElementById('monaco');
        require.config({
            paths: {
                'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/'+monacoVersion+'/min/vs',
                'namu': '../src/script',
            },
        });
        require.config({
            'vs/nls' : {
                availableLanguages: {
                    '*': 'ko'
                }
            }
        });
        require(['vs/editor/editor.main'], async () => {
            window.namu = window.namu || {};
                window.namu.toolbar = window.namu.toolbar || {};
                window.namu.toolbar.QuickAccess = function(TargetEditor) {
                    /* Private Properties */
                    let QuickAccess = this;
                    /* Private Methods */
                    let execute = function(describer) {
                        let TextModel = TargetEditor.getModel();
                        let selections = TargetEditor.getSelections();
                        let operations = [];
                        let selections_new = [];
                        TargetEditor.focus();
            
                        /* if type is not provided */
                        if(!describer.type) {
                            if(describer.bracket) describer.type = 'bracket';
                        }
                        switch(describer.type) {
                            case 'bracket':
                                /* if bracket is declared as shorthand */
                                if(typeof describer.bracket === 'string') {
                                    describer.bracket = {
                                        'open': describer.bracket,
                                        'close': describer.bracket
                                    };
                                }
                                for(let selection of selections) {
                                    selections_new.push(new monaco.Selection(selection.selectionStartLineNumber, selection.selectionStartColumn + describer.bracket.open.length, selection.endLineNumber, selection.endColumn + describer.bracket.open.length));
                                    operations.push({
                                        range: selection,
                                        text: describer.bracket.open + TextModel.getValueInRange(selection) + describer.bracket.close
                                    });
                                }
                                break;
                            default:
                                throw new Error(`Unknown describer type '${describer.type}'`);
                        }
                        TargetEditor.executeEdits('quickaccess', operations);
                        TargetEditor.setSelections(selections_new);
                    }
                    /* Public Methods */
                    QuickAccess.apply = function(event) {
                        event.preventDefault();
                        if(!arguments[0]) throw new TypeError(`Failed to execute 'namu.QuickAccess::apply': at least 1 argument required, but only 0 present.`);
                        if(arguments[0] instanceof Event) {
                            if(!(event instanceof Event) || !event.target) throw new Error('Invalid event fired');
                            let describerSet = {
                                'btn-bold': {"bracket":"'''"},
                                'btn-italic': {"bracket":"''"},
                                'btn-strike': {"bracket":"--"},
                                'btn-link': {"bracket":{"open":"[[","close":"]]"}},
                                'btn-file': {"bracket":{"open":"[[파일:","close":"]]"}},
                                'btn-ref': {"bracket":{"open":"[* ","close":"]"}},
                                'btn-template': {"bracket":{"open":"[include(","close":")]"}} 
                            }
                            let describer = describerSet[event.target.id];
                            if(!describer) throw new Error('Event fired but no describer');
                            execute(describer);
                        } else if(typeof arguments[0] === 'object') {
                            execute(arguments[0]);
                        }
                    };
            
                    /* Constructor */
                    if(!TargetEditor) throw new Error('Invalid target element');
                }
            namumark_register(monaco);
            if (window.matchMedia("(prefers-color-scheme: dark)").matches)
                ThisTheme = 'vs-dark'
            else
                ThisTheme = 'vs'
                
            window.monaco_namu = monaco.editor.create(target, {
                language: 'namumark',
                automaticLayout: true,
                wordWrap: true,
                theme: ThisTheme,
                renderWhitespace: 'all',
                minimap: { enabled: false },
                fontFamily: 'D2Coding, Consolas, "나눔고딕코딩", "Courier New", monospace',
                value: JSON.parse(DocContent).c
            });
            //target.querySelector('textarea').style.display = 'none';
            
            let quickaccess = new namu.toolbar.QuickAccess(window.monaco_namu);
            document.querySelectorAll('li.t.editor.top button').forEach((elem) => { 
                elem.addEventListener('click', quickaccess.apply); 
            });
        });
    });
})();
