## URL SHORT SETTINGS - NGINX (수정 예정)
```nginx
location ^~ /w/ {
    # CONNECT FROM /w/
    try_files $uri @PressDo;
}location @PressDo {
    # GO TO REAL DIRECTORY
    rewrite ^/w/(.*)$   /index.php?title=$1&args;
}location ^~ ^/(cache|includes|maintenance|languages|serialized|tests|images/deleted)/ {
    # BLOCK ACCESS TO THESE DIRECTORIES
    access_log off;
    log_not_found off;
    deny all;
}location ^~ ^/(docs|extensions|includes|maintenance|mw-config|resources|serialized|tests)/ {
    # ALLOW INTER ACCESS TO THESE DIRECTORIES
    internal;
}
```
 * 첫 번째 location 블럭은 도메인네임으로만 접속한 경우 미디어위키가 설치된 디렉토리로 보내주는 것이다.
 * 두 번째 location 블럭은 짧은 주소의 형태로 접속한 경우 이를 처리하기 위한 설정이다.
 * 세 번째 location 블럭에서는 실제로 rewrite 지시자를 써서 짧은 주소 형태의 주소를 제대로 처리하도록 한다.
 * 네 번째 location 블럭은 외부에서 접근하지 못하게 할 미디어위키의 디렉토리를 보호한다.
 * 다섯 번째 location 블럭은 미디어위키 소프트웨어 내부에서 사용할 수도 있는 디렉토리를 설정한다.


## ACL 처리 순서
1. 특정 사용자 ACL 확인
2. 문서 ACL 확인
3. usergroup ACL 확인(Priority 높은 것 부터 적용)
4. 이름공간 ACL 확인

## config.json 설정 내용
* Name: 위키 이름
* NameSpace: 위키 이름공간
* Domain: 위키 도메인
* ScriptPath: 스크립트 경로
* ViewerUri: 보기모드 경로
* Language: 언어 설정(/data/lang/ 하위의 .json 파일 이름)
* Title: 메인페이지 문서 이름
* 저작권 표시
  * CopyRight
  * HelpMail
  * TermsOfUse
  * SecPolicy
* 데이터베이스 정보
  * DBType: DB 종류(mysql 등...)
  * DBHost: DB 호스트
  * DBPort: DB 포트
  * DBName: 데이터베이스 이름
  * DBUser: 데이터베이스 사용자
  * DBPass: 데이터베이스 비밀번호
* 파일 업로드 설정
  * Uploadable: 파일업로드 허용여부
  * AllowFileExt: 허용 확장자(array)
  * CompressFile: 파일 업로드 시 용량 압축 여부
* 공개 수준
  * PublicLevel: 네임스페이스 기본값 설정 (0 - 전체 사용자, 1 - 회원만, 2 - 검증된 회원만, 3 - 비공개 위키)
  * AllowJoin: 가입 허용 여부
* 스킨 설정 - 로고 사진이랑 제목 텍스트를 동시에 적용하시면 둘이 겹쳐버리게 됩니다.(사진이 밑으로 감)
  * Skin: 스킨 이름
  * TitleText: 로고 텍스트
  * LogoWidth: 로고 너비(CSS 설정값)
* ACL 설정
  * ACLAutoTemplate: ACL 틀 자동 부착 여부
  * NameSpaceACL: 이름공간 ACL
* 허용 메일 설정
  * UseMailWhitelist: 허용 메일 목록 사용 여부
  * MailWhitelist: 허용 메일 목록(array)
* Extension: 적용할 확장 목록(array)
