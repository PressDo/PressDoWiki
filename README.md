# URL STRUCTURE

/:page/:title

# Class Structure
```js
class WikiCore {
    __construct()
    db_connect()
    show_error()
    latte_init()
    get_inner_layout()
    get_page()

    namespaces,
    config,
    lang,
    uri
    
    error: {
        code: str
        message: str
        errbox: bool
    }

    page: {
        name: viewName,
        title: 문서명,
        namespace: 이름공간,
        data: 
    }

    GET: {
        ...
    }
    POST: {
        ...
    }

    session: {
        menus: []
        member: {
            user_document_discuss
            username
            gravatar_url
        }
        ip
    }
}


class WikiSkin extends WikiCore {

}
```

# Backend Parameter
```js
wiki: {
    title, namespace
}
```