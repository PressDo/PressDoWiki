# PressDoWiki - Fast & Light PHP Wiki Engine
[![Issues](https://img.shields.io/github/issues/PressDo/PressDoWiki?style=for-the-badge)](https://github.com/PressDo/PressDoWiki)
[![Forks](https://img.shields.io/github/forks/PressDo/PressDoWiki.svg?style=for-the-badge)](https://github.com/PressDo/PressDoWiki)
[![Stars](https://img.shields.io/github/stars/PressDo/PressDoWiki.svg?style=for-the-badge)](https://github.com/PressDo/PressDoWiki)
[![License](https://img.shields.io/github/license/PressDo/PressDoWiki.svg?style=for-the-badge)](https://github.com/PressDo/PressDoWiki)
-------------------------
### 이 위키는 현재 제작 중입니다.
### Currently in development.
-------------------------
- 나무위키의 엔진인 the seed를 모방하여 만든 PHP 기반 위키입니다.
- 나무마크는 기본적으로 제공되지 않으며, 직접 설치하셔야 합니다.

 ### 개발 환경
 - PHP 8.2
 - MariaDB

 ### 요구 사항
 - PHP가 설치되어 있어야 합니다.
 - composer로 패키지 설치가 가능해야 합니다.
 - MySQL, MariaDB 중 하나가 설치되어 있어야 합니다.
 - 확인이 필요한 항목: ~~PostgreSQL, CUBRID, Oracle Database, MSSQL, Firebird, IBM DB2, SQLite~~
 - php-geoip (PECL 확장) 또는 Maxmind GeoIP2 데이터베이스 (city)가 있어야 합니다: php-geoip가 우선 적용됩니다.
 - 파일이 업로드될 공간이 있어야 합니다. (S3, Local 중 선택)
 - ngram Parser가 지원되는 데이터베이스를 사용하거나 별도의 검색 엔진 소프트웨어가 설치되어 있어야 합니다.

 ### 사전 설정
 - 파일 업로드 사용 여부를 설정하세요.
 - 지원되는 Database가 설치되어 있어야 합니다.
 - 위키에 업로드될 파일 크기에 맞게 php.ini에서 upload_max_filesize와 post_max_size를 조정해 주세요.
 - [templates/server.nginx](https://github.com/PressDo/PressDoWiki/blob/dev/templates/server.nginx)를 참고해 웹 서버를 세팅해 주세요.

 ### 설치 과정
 테스트 위키에 추후 업로드 예정입니다.

 ### 지원 스킨
 - ~~senkawa~~ (저작권 문제로 배포하지 않습니다.)
 - liberty (예정)
 - vector (예정)
 - buma (예정)