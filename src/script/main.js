function hiddencontents(a){
    b = document.getElementById(a);
    c = document.getElementById('content-'+a);
    if(b.getAttribute('pressdo-toc-fold') == 'hide'){
        b.setAttribute('pressdo-toc-fold', 'show');
        c.setAttribute('pressdo-toc-fold', 'show');
    }else{
    b.setAttribute('pressdo-toc-fold', 'hide');
        c.setAttribute('pressdo-toc-fold', 'hide');
    }
}

function sb(uri){
    f = document.getElementById('search-keyword').value;
    if(f == '')
        return false;
    location.href = uri + f;
}

function editorMenu(action, uri, msg) {  
    var f = document.getElementById('editForm');
    n = document.getElementById('pressdo-anchor')
    if(action == 'preview'){
        f.action = uri;
        var parent = n.parentNode;
        parent.insertBefore(f, parent.childNodes[6]);
    }
    if(action == 'save'){
        if(!document.editForm.agree.checked){
            alert(msg);
            return false;
        }
        f.action = uri; 
    }
    f.submit();
}

Hide = (v) => document.getElementById(v).setAttribute('style', 'display:none;');
Show = (v) => document.getElementById(v).setAttribute('style', 'display:block;');
function login(){
    var user = document.getElementById("username");
    var pwd = document.getElementById("password");
    if(user.value=="") {
        Show('erruser');
        Hide('errpwd');
        return false;
    }else if(pwd.value==""){
        Hide('erruser');
        Show('errpwd');
        return false;
    }else{
        Hide('erruser');
        Hide('errpwd');
        var f = document.loginform;
        f.submit();
    }
}

function chkmail(){
    var mail = document.getElementById("email");
    if(mail.value=="") {
        Show('errmail');
        return false;
    }else{
        Hide('errmail');
        var f = document.regform;
        f.submit();
    }
}

function signupcheck(){
    var user = document.getElementById("username");
    var pass = document.getElementById("password");
    var pass2 = document.getElementById("password2");
    if(user.value==''){
        Show('erruser1');
        Hide('erruser2');
        Hide('erruser3');
        Hide('errpwd');
        Hide('errpwd21');
        Hide('errpwd22');
    }else if(/^[A-Za-z][A-Za-z0-9_]{2,31}$/.test(user.value) === false){
        Hide('erruser1');
        Hide('erruser2');
        Show('erruser3');
        Hide('errpwd');
        Hide('errpwd21');
        Hide('errpwd22');
    }else if(pass.value==''){
        Hide('erruser1');
        Hide('erruser2');
        Hide('erruser3');
        Show('errpwd');
        Hide('errpwd21');
        Hide('errpwd22');
    }else if(pass2.value==''){
        Hide('erruser1');
        Hide('erruser2');
        Hide('erruser3');
        Hide('errpwd');
        Show('errpwd21');
        Hide('errpwd22');
    }else if(pass.value !== pass2.value){
        Hide('erruser1');
        Hide('erruser2');
        Hide('erruser3');
        Hide('errpwd');
        Hide('errpwd21');
        Show('errpwd22');
    }else if(user.exist){
        Hide('erruser1');
        Show('erruser2');
        Hide('erruser3');
        Hide('errpwd');
        Hide('errpwd21');
        Hide('errpwd22');
    }else{
        var f = document.getElementById('signupform');
        f.submit();
    }
}
