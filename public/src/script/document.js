SH = (v,s) => v.setAttribute('v', s),
a = {
    g: (e,f) => e.getAttribute(f),
    s: (e,f,g) => e.setAttribute(f,g)
},
c = {
    a: (a,b) => a.classList.add(b),
    r: (a,b) => a.classList.remove(b),
    c: (a,b) => a.classList.contains(b)
}
ce = (a) => document.createElement(a),
e =  (e,a,c) => e.addEventListener(a, c),
g =  (e) => document.getElementById(e),
q =  (e) => document.querySelector(e),
qa =  (e) => document.querySelectorAll(e),
p = function (p,c) {
    if(p != c && c.parentNode != p)
        return true
    else
        return false
}
v = {
    b: (e) => SH(g(e),'block'),
    h: (e) => SH(g(e),'hidden'),
    n: (e) => SH(g(e),'none')
}

// KaTeX
document.addEventListener("DOMContentLoaded", function () {
    renderMathInElement(document.body, {
        delimiters: [
            { left: "[math(", right: ")]", display: false },
            { left: "<math>", right: "</math>", display: false },
        ],
    })
})

// 문단 접기
qa('.w .wiki-heading').forEach(r => {
    e(r, 'click', () => {
        var c = r.nextSibling
        
        if(a.g(r, 'fold') == 'true'){
            a.s(r, 'fold', 'false')
            a.s(c, 'fold', 'false')
        }else{
            a.s(r, 'fold', 'true')
            a.s(c, 'fold', 'true')
        }
    })
})

qa('a.wiki-fn-content').forEach(r => {
    e(r, 'mouseover', () => {
        var id =  r.href.substring(r.href.indexOf('#'))
        q('div.popper__inner').innerHTML = q('span.footnote-list>span'+id).parentNode.innerHTML
    })
})

// 접기
qa('.w dl.wiki-folding dt').forEach(r => {
    e(r, 'click', () => {
        var s = r.nextSibling;
        //s.style.cssText = 'max-width:' + r.offsetWidth + 'px;max-height:' + r.offsetHeight + 'px;'

        if(c.c(s, 'unfolded'))
            c.r(s, 'unfolded')
        else
            c.a(s, 'unfolded')

        //s.style.cssText = ''
    })
})  