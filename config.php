<?php
use PressDo\PressDo;
require_once 'PressDoLib.php';
// PressDo Global Config
// Automatically Generated.

// 위키 이름
$conf['Name'] = '';

// 위키 이름공간
$conf['NameSpace'] = '';

// 위키 도메인
$conf['Domain'] = '';

// 스크립트 경로
$conf['ScriptPath'] = '/';

// 보기모드 경로
$conf['ViewerUri'] = '/w/';

// 저작권 표시
$conf['CopyRight'] = 'PRASEOD-';
$conf['HelpMail'] = '<a href="admin@prws.kr">Contact Us</a>';
$conf['TermsOfUse'] = '';
$conf['SecPolicy'] = '';

// 데이터베이스 정보
$conf['DBType'] = '';
$conf['DBHost'] = '';
$conf['DBPort'] = '';
$conf['DBName'] = '';
$conf['DBUser'] = '';
$conf['DBPass'] = '';

// 파일 업로드 설정
$conf['Uploadable'] = true; // 업로드 허용
$conf['AllowFileExt'] = array('PNG', 'JPG', 'JPEG', 'GIF'); // 확장자 허용
$conf['CompressFile'] = true; // 사진용량압축

// 공개 수준 - 0: 전체 사용자, 1: 회원만, 2: 검증된 회원만, 3: 비공개 위키
$conf['PublicLevel'] = 0;
$conf['AllowJoin'] = true; // 가입 허용

// 스킨 설정
// 로고 사진이랑 제목 텍스트를 동시에 적용하시면 둘이 겹쳐버리게 됩니다.(사진이 밑으로 감)
$conf['Skin'] = 'default'; // 스킨이름
$conf['TitleText'] = 'PressDoWiki'; // 제목 텍스트
$conf['LogoWidth'] = '6.6rem'; // 로고 영역 너비

// ACL 틀 자동화
$conf['ACLAutoTemplate'] = true;

// 메인페이지 문서명
$conf['Title'] = '대문';

// 허용 메일 목록
$conf['UseMailWhitelist'] = 1;
$conf['MailWhitelist'] = array(
'gmail.com',
'naver.com',
'kakao.com',
'prws.kr'
);

// 확장 적용
$conf['Extension'] = array(
'TestExtension'
);
?>
