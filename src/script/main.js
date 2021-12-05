SH = (v,s) => v.setAttribute('v', s),
a = {
    g: (e,f) => e.getAttribute(f),
    s: (e,f,g) => e.setAttribute(f,g)
},
ce = (a) => document.createElement(a),
e =  (e,a,c) => e.addEventListener(a, c),
g =  (e) => document.getElementById(e),
q =  (e) => document.querySelector(e),
qa =  (e) => document.querySelectorAll(e),
v = {
    b: (e) => SH(g(e),'block'),
    h: (e) => SH(g(e),'hidden'),
    n: (e) => SH(g(e),'none')
}
Ctrl = Alt = false;

document.onkeyup = (e) => {
    switch(e.keyCode){
        case 17:
            Ctrl = false;
            break;
        case 18:
            Alt = false;
            break;
    }
}
// 단축키
document.onkeydown = (e) => {
    var t = document.activeElement.tagName;
    if(t !== 'INPUT' && t !== 'SELECT' && t !== 'TEXTAREA' && Ctrl === false && Alt === false){
        switch (e.keyCode){
            case 17:
                Ctrl = true;
                break;
            case 18:
                Alt = true;
                break;
            case 70:
                location.href = '/rd.php?t=FrontPage';
                break;
            case 67:
                location.href = '/rd.php?t=RecentChanges';
                break;
            case 68:
                location.href = '/rd.php?t=RecentDiscuss';
                break;
            case 65:
                location.href = '/rd.php?t=random';
                break;
            case 69:
                location.href = '/rd.php?t=edit';
                break;
        }
    }
}

// 나무마크
qa('.nm-cb').forEach(c => {
    e(c,'click', (e) => e.cancelBubble=true);
});
qa('.hidden-trigger').forEach(h => {
    e(h,'click', () => {
        var at = a.g(h,'id');
        var c = g('content-'+at);
        if(a.g(g(at),'data-pressdo-toc-fold') == 'hide'){
            a.s(g(at),'data-pressdo-toc-fold', 'show');
            a.s(c,'data-pressdo-toc-fold', 'show');
        }else{
            a.s(g(at),'data-pressdo-toc-fold', 'hide');
            a.s(c,'data-pressdo-toc-fold', 'hide');
        }
    })
});

// 검색창 버튼
qa("#sb").forEach(sb => {
    e(sb,'click',() => {
        var f = g('search-keyword').value;
        if(f == '')
            return false;
        location.href = a.g(sb,'goto') + f;
    });
});

// 편집창
qa("#ef").forEach(ee => {
	e(ee,"click",() => {
        var ea = a.g(ee,'editor-action');
        var f = g('editForm');
        if(ea == 'preview'){
            var p = g('data-pressdo-anchor').parentNode;
            p.insertBefore(f, p.childNodes[6]);
        }
        if(ea == 'save' && document.editForm.agree.checked === false){
            alert(a.g(ee,'editor-msg'));
            return false;
        }
        f.action = a.g(ee,'editor-uri');
        f.submit();
    });
});

// 창
if(g('delb')){
    var ee = g('delb');
	e(ee,"click",() => {
        var f = g('editForm');
        if(document.editForm.agree.checked === false){
            alert(a.g(ee,'editor-msg'));
            return false;
        }
        //f.action = a.g(ee,'editor-uri');
        f.submit();
    });
}

// 메일
if(g('chkmail')){
    e(g('chkmail'),'click', () => {
        if(g("email").value=="") {
            v.b('errmail');
            return false;
        }else{
            v.n('errmail');
            document.regform.submit();
        }
    });
}

// 가입
if(g('signup')){
    e(g('signup'),'click', () => {
        var u = g("username");
        var p = g("password");
        var p2 = g("password2");
        if(u.value==''){
            v.b('erruser1');
            v.n('erruser2');
            v.n('erruser3');
            v.n('errpwd');
            v.n('errpwd21');
            v.n('errpwd22');
        }else if(/^[A-Za-z][A-Za-z0-9_]{2,31}$/.test(u.value) === false){
            v.n('erruser1');
            v.n('erruser2');
            v.b('erruser3');
            v.n('errpwd');
            v.n('errpwd21');
            v.n('errpwd22');
        }else if(p.value==''){
            v.n('erruser1');
            v.n('erruser2');
            v.n('erruser3');
            v.b('errpwd');
            v.n('errpwd21');
            v.n('errpwd22');
        }else if(p2.value==''){
            v.n('erruser1');
            v.n('erruser2');
            v.n('erruser3');
            v.n('errpwd');
            v.b('errpwd21');
            v.n('errpwd22');
        }else if(p.value !== p2.value){
            v.n('erruser1');
            v.n('erruser2');
            v.n('erruser3');
            v.n('errpwd');
            v.n('errpwd21');
            v.b('errpwd22');
        }else{
            g('signupform').submit();
        }
    });
}

// condition 
qa('#cs').forEach(c => {
    e(c,'change', (e) => {
        var i = a.g(c,'i');
        var ct = q('#ct[i='+i+']');
        var ct_r = q('#ct_r[i='+i+']');
        if(e.target.value !== 'perm'){
            SH(ct,'none');
            ct_r.type = 'text';
            ct_r.name = 'target_name';
            ct.name = '';
        }else{
            SH(ct,'inline-block');
            ct_r.type = 'hidden';
            ct_r.name = '';
            ct.name = 'target_name';
        }
    })
});

// ACL기간
qa('#duration').forEach(s => {
    var i = a.g(s,'i');
    e(s,'change', (e) => {
        var du = q('#dr_e[i='+i+']');
        var d = q('#dr[i='+i+']');
        var f = q('#du[i='+i+']');
        if(e.target.value !== 'raw'){
            SH(f,'none');
            du.type = 'hidden';
            d.value = e.target.value;
        }else{
            SH(f,'inline-block');
            du.type = 'text';
            du.value = '';
        }
    });
});

// ACL그룹-기간
if(g('dur')){
    var f = g('du');
    var d = g('dr');
    var du = g('dr_e');
    e(g('dur'), 'change', (e) => {
        if(e.target.value !== 'raw'){
            SH(f,'none');
            du.type = 'hidden';
            d.value = e.target.value;
        }else{
            SH(f,'inline-block');
            du.type = 'text';
            du.value = '';
        }
    })
}

// ACL기간(R)
qa('#dr_e').forEach(d => {
    var i = a.g(d,'i');
    e(d,'input', (e) => {
        if(isNaN(e.target.value))
            d.class = 'i';
        else{
            d.class = '';
            q('#dr[i="'+i+'"]').value = q('#du[i='+i+']').value * e.target.value;
        }
    });
});

// ACL기간단위
qa('#du').forEach(u => {
    e(u,'change', (e) => {
        var i = a.g(u,'i');
        q('#dr[i="'+i+'"]').value = q('#dr_e[i='+i+']').value * e.target.value;
    });
});

// ACL삭제
qa('button[delete-info]').forEach(r => {
    e(r,'click', () => {
        if(!confirm(a.g(r,'msg'))){
            return false;
        }else{
            g('rft').value = a.g(r,'delete-info');
            g('rf').submit();
        }
    });
});

// ACL그룹-삭제
qa('#acl_delete').forEach(r => {
    e(r,'click', () => {
        var i = a.g(r,'acllog_id');
        v.b('remove_win');
        g('id_to_remove').innerText = i;
        g('idto_remove').value = i;
    });
});
qa('#cancel_btn').forEach(r => {
    e(r,'click', () => {
        v.n('remove_win');
        v.n('add_win');
    });
});

// ACL그룹-추가
if(g('nag')){
    e(g('nag'), 'click', () => {
        v.b('add_win');
    });
}

// ACL그룹삭제
qa('.rmag').forEach(r => {
    e(r,'click', (e) => {
        e.preventDefault();
        var co = confirm(a.g(r,'msg'));
        if(co === false){
            return false;
        }else if(co === true){
            var f = ce('form');
            var i = ce('input');
            i.type = 'hidden';
            i.name = 'delnm';
            i.value = a.g(r,'d_target');
            f.appendChild(i);
            f.method = 'POST';
            f.action = window.location.href;
            document.body.appendChild(f);
            f.submit();
        }
    });
});

// ACL그룹-사용자추가
if(q('select[name=mode]')){
    e(q('select[name=mode]'),'change', (e) => {
        var i = g('vl');
        if(e.target.value == 'ip'){
            i.name = 'ip';
            i.placeholder = 'CIDR';
        }else if(e.target.value == 'username'){
            i.name = 'username';
            i.placeholder = q('option[value=username]').innerText;
        }
    });
}

// 역사 체크박스
qa('input[data-pressdo-history-radio]').forEach(r => {
    e(r, 'input', () => {
        if(a.g(r,'name') == 'oldrev'){
            //qa('input[data-pressdo-history-radio][value]')
        }else if(a.g(r,'name') == 'rev'){

        }
    });
});

// 파일 업로드
if(g('fubtn')){
    e(g('fubtn'), 'click', () => {
        document.all.fileInput.click();
    });
    var f = g('fileInput');
    e(f, 'change', () => {
        var fn = f.files[0].name.split('.');
        var fil = fn.pop();
        g('fakeFileInput').value = f.value;
        g('documentInput').value = a.g(f,'ns')+':'+fn[fn.length - 1]+'.'+fil.toLowerCase();
    });
}

/*                        // 분류 위치 조정
                        var f = document.getElementById('categories');
                        var parent = f.parentNode.parentNode;
                        parent.insertBefore(f, parent.childNodes[0]);*/
                        /* 
    딴곳누르면 드롭다운 사라지는 기능(예정)
    document.querySelector("body").addEventListener('click', event => {
        if(event.target == event.currentTarget.querySelector("#content-nav-menu"))
            return;
        b = document.getElementById('nav-menu');
        c = document.getElementById('content-nav-menu');
        if(b.getAttribute('data-pressdo-toc-fold') == 'show'){
            b.setAttribute('data-pressdo-toc-fold', 'hide');
            c.setAttribute('data-pressdo-toc-fold', 'hide');
        }
    });
*/
