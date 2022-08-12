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

before = 0
Ctrl = Alt = false
Lock = true
document.onkeyup = (e) => {
    switch(e.keyCode){
        case 17:
            Ctrl = false
            break
        case 18:
            Alt = false
            break
    }
}
// 단축키
document.onkeydown = (e) => {
    var t = document.activeElement.tagName
    if(t !== 'INPUT' && t !== 'SELECT' && t !== 'TEXTAREA' && Ctrl === false && Alt === false){
        switch (e.keyCode){
            case 17:
                Ctrl = true
                break
            case 18:
                Alt = true
                break
            case 70:
                location.href = '/rd.php?t=FrontPage'
                break
            case 67:
                location.href = '/rd.php?t=RecentChanges'
                break
            case 68:
                location.href = '/rd.php?t=RecentDiscuss'
                break
            case 65:
                location.href = '/rd.php?t=random'
                break
            case 69:
                location.href = '/rd.php?t=edit'
                break
        }
    }
}

qa('.hidden-trigger').forEach(h => {
    e(h,'click', () => {
        var at = a.g(h,'id')
        var c = g('content-'+at)
        if(a.g(g(at),'data-pressdo-toc-fold') == 'hide'){
            a.s(g(at),'data-pressdo-toc-fold', 'show')
            a.s(c,'data-pressdo-toc-fold', 'show')
        }else{
            a.s(g(at),'data-pressdo-toc-fold', 'hide')
            a.s(c,'data-pressdo-toc-fold', 'hide')
        }
    })
})

// 검색창 버튼
qa("#sb").forEach(sb => {
    e(sb,'click',() => {
        var f = g('search-keyword').value
        if(f == '')
            return false
        location.href = a.g(sb,'goto') + f
    })
})

// 편집기
qa('button.e.editor.top').forEach(r => {
    e(r, 'click', e => {
        var a = q('div#'+e.target.id+'.editor')
        var b = q('button.e.a.editor.top');
        
        if(b.id == e.target.id){
            return false
        }else{
            var d = q('div.a.editor')
            var f = q('li.t.editor.top')
            var g = q('button.tr.editor.top')
            c.r(g,'tr')
            c.r(b,'a')
            c.a(b,'tr')
            c.a(e.target,'a')
            c.r(d,'a')
            c.a(a,'a')
            if(e.target.id == 'm'){
                window.monaco_namu.setValue(q('div#r textarea.editor').value)
                SH(f,'')
            }else{
                SH(f,'none')
                q('div#r textarea.editor').value = window.monaco_namu.getValue()
            }
            if(e.target.id == 'p')
                getPreview()
        }
    })
})

// 미리보기
if(q('div#r textarea.editor') !== null){
    function getPreview() {
        var x = q('div#r textarea.editor').value
        g('editForm').content.value = x
        const xhr = new XMLHttpRequest()
        const data = new URLSearchParams({
            text: x
        })

        xhr.open('POST', window.location.protocol + '//' + window.location.host + apiuri)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8')
        xhr.onreadystatechange = () => {
            if(xhr.readyState === xhr.DONE && xhr.status === 200) {
                q('div#p.editor').innerHTML = xhr.responseText
            }
        }
        xhr.send(data)
    }
}
// 편집창
if(g('ef')){
    var ee = g('ef')
	e(ee,"click",() => {
        var f = g('editForm')
        var x = q('button.a.editor.top').id
        var y = q('button.tr.editor.top').id
        if(document.editForm.agree.checked === false){
            alert(a.g(ee,'editor-msg'))
            return false
        }
        if((x == 'p' && y == 'm') || x == 'm') {
            q('div#r textarea.editor').value = window.monaco_namu.getValue()
        }

        f.action = a.g(ee,'editor-uri')
        f.submit()
    })
}

// 창
if(g('delb')){
    var ee = g('delb')
	e(ee,"click",() => {
        var f = g('editForm')
        if(document.editForm.agree.checked === false){
            alert(a.g(ee,'editor-msg'))
            return false
        }
        //f.action = a.g(ee,'editor-uri')
        f.submit()
    })
}

// 메일
if(g('chkmail')){
    e(g('chkmail'),'click', () => {
        if(g("email").value=="") {
            v.b('errmail')
            return false
        }else{
            v.n('errmail')
            document.regform.submit()
        }
    })
}

// 가입
if(g('signup')){
    e(g('signup'),'click', () => {
        var u = g("username")
        var p = g("password")
        var p2 = g("password2")
        if(u.value==''){
            v.b('erruser1')
            v.n('erruser2')
            v.n('erruser3')
            v.n('errpwd')
            v.n('errpwd21')
            v.n('errpwd22')
        }else if(/^[A-Za-z][A-Za-z0-9_]{2,31}$/.test(u.value) === false){
            v.n('erruser1')
            v.n('erruser2')
            v.b('erruser3')
            v.n('errpwd')
            v.n('errpwd21')
            v.n('errpwd22')
        }else if(p.value==''){
            v.n('erruser1')
            v.n('erruser2')
            v.n('erruser3')
            v.b('errpwd')
            v.n('errpwd21')
            v.n('errpwd22')
        }else if(p2.value==''){
            v.n('erruser1')
            v.n('erruser2')
            v.n('erruser3')
            v.n('errpwd')
            v.b('errpwd21')
            v.n('errpwd22')
        }else if(p.value !== p2.value){
            v.n('erruser1')
            v.n('erruser2')
            v.n('erruser3')
            v.n('errpwd')
            v.n('errpwd21')
            v.b('errpwd22')
        }else{
            g('signupform').submit()
        }
    })
}

// condition 
qa('#cs').forEach(c => {
    e(c,'change', (e) => {
        var i = a.g(c,'i')
        var ct = q('#ct[i='+i+']')
        var ct_r = q('#ct_r[i='+i+']')
        if(e.target.value !== 'perm'){
            SH(ct,'none')
            ct_r.type = 'text'
            ct_r.name = 'target_name'
            ct.name = ''
        }else{
            SH(ct,'inline-block')
            ct_r.type = 'hidden'
            ct_r.name = ''
            ct.name = 'target_name'
        }
    })
})

// ACL기간
qa('#duration').forEach(s => {
    var i = a.g(s,'i')
    e(s,'change', (e) => {
        var du = q('#dr_e[i='+i+']')
        var d = q('#dr[i='+i+']')
        var f = q('#du[i='+i+']')
        if(e.target.value !== 'raw'){
            SH(f,'none')
            du.type = 'hidden'
            d.value = e.target.value
        }else{
            SH(f,'inline-block')
            du.type = 'text'
            du.value = ''
        }
    })
})

// ACL그룹-기간
if(g('dur')){
    var f = g('du')
    var d = g('dr')
    var du = g('dr_e')
    e(g('dur'), 'change', (e) => {
        if(e.target.value !== 'raw'){
            SH(f,'none')
            du.type = 'hidden'
            d.value = e.target.value
        }else{
            SH(f,'inline-block')
            du.type = 'text'
            du.value = ''
        }
    })
}

// ACL기간(R)
qa('#dr_e').forEach(d => {
    var i = a.g(d,'i')
    e(d,'input', (e) => {
        if(isNaN(e.target.value))
            d.class = 'i'
        else{
            d.class = ''
            q('#dr[i="'+i+'"]').value = q('#du[i='+i+']').value * e.target.value
        }
    })
})

// ACL기간단위
qa('#du').forEach(u => {
    e(u,'change', (e) => {
        var i = a.g(u,'i')
        q('#dr[i="'+i+'"]').value = q('#dr_e[i='+i+']').value * e.target.value
    })
})

// ACL삭제
qa('button[delete-info]').forEach(r => {
    e(r,'click', () => {
        if(!confirm(a.g(r,'msg'))){
            return false
        }else{
            g('rft').value = a.g(r,'delete-info')
            g('rf').submit()
        }
    })
})

// ACL그룹-삭제
qa('#acl_delete').forEach(r => {
    e(r,'click', () => {
        var i = a.g(r,'acllog_id')
        v.b('remove_win')
        g('id_to_remove').innerText = i
        g('idto_remove').value = i
    })
})
qa('#cancel_btn').forEach(r => {
    e(r,'click', () => {
        v.n('remove_win')
        v.n('add_win')
    })
})

// ACL그룹-추가
if(g('nag')){
    e(g('nag'), 'click', () => {
        v.b('add_win')
    })
}

// ACL그룹삭제
qa('.rmag').forEach(r => {
    e(r,'click', (e) => {
        e.preventDefault()
        var co = confirm(a.g(r,'msg'))
        if(co === false){
            return false
        }else if(co === true){
            var f = ce('form')
            var i = ce('input')
            i.type = 'hidden'
            i.name = 'delnm'
            i.value = a.g(r,'d_target')
            f.appendChild(i)
            f.method = 'POST'
            f.action = window.location.href
            document.body.appendChild(f)
            f.submit()
        }
    })
})

// ACL그룹-사용자추가
if(q('select[name=mode]')){
    e(q('select[name=mode]'),'change', (e) => {
        var i = g('vl')
        if(e.target.value == 'ip'){
            i.name = 'ip'
            i.placeholder = 'CIDR'
        }else if(e.target.value == 'username'){
            i.name = 'username'
            i.placeholder = q('option[value=username]').innerText
        }
    })
}

// 역사 체크박스
qa('input[data-pressdo-history-radio]').forEach(r => {
    e(r, 'input', () => {
        if(a.g(r,'name') == 'oldrev'){
            //qa('input[data-pressdo-history-radio][value]')
        }else if(a.g(r,'name') == 'rev'){

        }
    })
})

// 파일 업로드
if(g('fubtn')){
    e(g('fubtn'), 'click', () => {
        document.all.fileInput.click()
    })
    var f = g('fileInput')
    e(f, 'change', () => {
        var fn = f.files[0].name.split('.')
        var fil = fn.pop()
        g('fakeFileInput').value = f.value
        g('documentInput').value = a.g(f,'ns')+':'+fn[fn.length - 1]+'.'+fil.toLowerCase()
    })
}
qa('div[dropdown-toggle]').forEach(r => {
    e(r, 'click', () => {
        var x = a.g(r,'dropdown-toggle')
        var y = q('input[dropdown-input='+x+']')
        var z = q('#list-dropdown-menu[dropdown-name='+x+']')
        if(c.c(g(x), 'rot')){
            c.r(g(x),'rot')
            a.s(z, 'data-pressdo-toc-fold', 'hide')
            a.s(r, 'data-pressdo-toc-fold', 'hide')
            y.blur()
        }else{
            c.a(g(x), 'rot')
            a.s(z, 'data-pressdo-toc-fold', 'show')
            a.s(r, 'data-pressdo-toc-fold', 'show')
            y.focus()
        }
    })
})
qa('li[dropdown-option]').forEach(r => {
    e(r, 'click', () => {
        var x = a.g(r, 'dropdown-option')
        var y = q('input[dropdown-input=arrow_'+x+']')
        var z = q('#list-dropdown-menu[dropdown-name=arrow_'+x+']')
        q('span[dropdown-selected='+x+']').innerText = r.innerText
        a.s(q('input[dropdown-input=arrow_'+x+']'), 'placeholder', '')
        c.r(g('arrow_'+x),'rot')
        a.s(z, 'data-pressdo-toc-fold', 'hide')
        a.s(q('div[dropdown-toggle=arrow_'+x+']'), 'data-pressdo-toc-fold', 'hide')
        y.blur()
    })
})

// 다른 곳 클릭하면 팝업 닫힘
e(document, 'click', e => {
    var x = q('.hidden-trigger')
    var y = g('content-nav-menu')

    if(g('list-dropdown-menu'))
        var z = g('list-dropdown-menu')

    if(p(g('nav-menu'),e.target)){
    
        //c.r(g(x),'rot')
        //a.s(y, 'data-pressdo-toc-fold', 'hide')
        if(z)
            z.blur()
    }
})

// 요약 글자수
if(g('logInput')){
    e(g('logInput'), 'input', () => {
        var cnt = g('logInput').value.length
        var t = q('label.comment[for=logInput]').innerText
        var b = ' ('+before+'/190)'
        var a = ' ('+cnt+'/190)'
        if(before === 0 && cnt > 0) // 0 > 1
            q('label.comment[for=logInput]').innerText += a
        else if(before > 0 && cnt === 0) // 1 > 0
            q('label.comment[for=logInput]').innerText = t.replace(b, '')
        else if(before < cnt)
            q('label.comment[for=logInput]').innerText = t.replace(b, a)
        else if(before > cnt)
            q('label.comment[for=logInput]').innerText = t.replace(b, a)
        before = cnt;
    })
}