# kintai

## 前提条件
- Gitがインストール済みであること
- DockerおよびDocker Composeがインストール済みであること

## 環境構築

### Clone
```bash
git clone git@github.com:nihil0000/furima-mock.git .
```

＊ MySQLはOSによって起動しない場合があるため、それぞれのOSに合わせてdocker-compose.ymlファイルを編集してください \
＊ 開発環境がMacOS (Appleチップ) mysql, phpmyadminにて`platform: linux/amd64`を指定しています \
＊ 他のOSでMySQLが起動しない場合
- [Docker公式ドキュメント](https://docs.docker.com/)をご参照ください。

### Build
```bash
make init
```

## 使用技術
- PHP 8.3.19
- Larabel 10.48.29
- MySQL 8.0.26

## ER図
<img src="docs/er-diagram.png" alt="ER図" width="500">

## アクセスURL
- 開発環境
    - http://localhost
- phpMyAdmin
    - http://localhost:8080

## テストアカウント
- 一般ユーザ
   - name: general
   - email: general@example.com
   - password: password
- 管理ユーザ
   - name: admin
   - email: admin@example.com
   - password: password

## PHPUnitを利用したテスト
```bash
# Create database
docker-compose exec mysql bash
mysql -u root -p
create database test_database; # password: root

# Migrate
docker compose exec php bash
php artisan migrate:fresh --env=testing

# Testing
./vendor/bin/phpunit
```
