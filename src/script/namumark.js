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

// 나무마크
qa('.wiki-heading').forEach(r => {
    e(r, 'click', () => {
        var id = r.id
        var c = g('content-'+id)
        if(a.g(r, 'fold') == 'true'){
            a.s(r, 'fold', 'false')
            a.s(c, 'fold', 'false')
        }else if(a.g(r, 'fold') == 'false'){
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