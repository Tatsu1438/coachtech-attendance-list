# coachtech-attendance-list

## サービス名

coachtech勤怠管理アプリ

## サービス概要

ある企業が開発した独自の勤怠管理アプリ

制作の背景と目的

ユーザーの勤怠と管理を目的とする

目標

初年度でのユーザー数1000人達成

ターゲットユーザー

	10-30代の社会人

ターゲットブラウザ/os

	PC:Chrome/Firefox/Safari

作業範囲

	設計、コーディング、テスト

納品方法

	Githubでのリポジトリ共有

## 環境構築

## Dockerビルド

	1. 任意のディレクトリを作成
    2. git clone git@github.com:Tatsu1438/coachtech-freemarket.git
    3. cd coachtech-attendance-list
    4. DockerDesktopアプリを立ち上げる
    5. docker-compose up -d --build

## Laravel環境構築

    docker-compose exec php bash
    composer install

## 環境変数

.env ファイルを作成して以下のように設定してください

    環境変数を以下に変更
	cp .env.example .env
	
	MAIL_MAILER=smtp
	MAIL_HOST=mailhog
	MAIL_PORT=1025
	MAIL_USERNAME=null
	MAIL_PASSWORD=null
	MAIL_ENCRYPTION=null
	MAIL_FROM_ADDRESS=admin@example.com
	MAIL_FROM_NAME="${APP_NAME}"

	DB_CONNECTION=mysql
	DB_HOST=coachtech-attendance-mysql
	DB_PORT=3306
	DB_DATABASE=laravel_db
	DB_USERNAME=laravel_user
	DB_PASSWORD=laravel_pass

	ADMIN_EMAIL=admin@example.com
	ADMIN_PASSWORD=admin12345


    DB_CONNECTION=mysql
	DB_HOST=mysql_test
	DB_PORT=3306
	DB_DATABASE=laravel_test_db
	DB_USERNAME=laravel_user
	DB_PASSWORD=laravel_pass

## アプリケーションキーの作成

	php artisan key:generate

	php artisan config:clear
	php artisan cache:clear
	php artisan config:cache

## マイグレーション(test用と本番用)の作成&実行

	php artisan migrate
 	php artisan migrate --env=testing

## テスト方法

以下のコマンドでユニットテストを実行できます

	docker-compose exec php bash
	php artisan test --env=testing

## シーディングの作成&実行

    php artisan db:seed

## シンボリックリンクの作成

    php artisan storage:link

## 使用技術（実行環境）

php:8.1-fpm

mysql:8.0

nginx:1.21.1

Laravel:8.x（バージョン 8.75 以降互換）

mailhog

## 開発環境

URL:

・画面: http://localhost/

・ユーザー登録: http://localhost/register

・ユーザーログイン: http://localhost/login

・管理者ログイン: http://localhost/admin/login

  管理者ログイン情報
  
    email: admin@example.com
    password: admin12345

管理者ログイン情報

    user_name: サンプル太郎
	email: sample@example.com
    password: sample12345

・phpMyAdmin: http://localhost:8080/

・mailhog: http://localhost:8025
   

## ER図

![ER図](src/public/er_diagram.png)


## テーブル一覧

### usersテーブル

| カラム名                  | 型             | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|---------------------------|----------------|-------------|------------|----------|-------------|
| id                        | unsigned bigint| ○           |            | ○        |             |
| user_name                 | varchar(255)   |             |            | ○        |             |
| email                     | varchar(255)   |             | ○          | ○        |             |
| password                  | varchar(255)   |             |            | ○        |             |
| status                    | varchar(255)   |             |            | ○        |             |
| email_verified_at         | timestamp      |             |            | ○        |             |
| two_factor_secret         | varchar(255)   |             |            |          |             |
| two_factor_recovery_codes | text           |             |            |          |             |
| two_factor_confirmed_at   | timestamp      |             |            |          |             |
| remember_token            | varchar(100)   |             |            |          |             |
| created_at                | timestamp      |             |            |          |             |
| updated_at                | timestamp      |             |            |          |             |

### attendancesテーブル

| カラム名        | 型             | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|-----------------|----------------|-------------|------------|----------|-------------|
| id              | unsigned bigint| ○           |            | ○        |             |
| user_id         | unsigned bigint|             |            | ○        | users(id)   |
| work_date       | date           |             |            | ○        |             |
| clock_in        | time           |             |            |          |             |
| clock_out       | time           |             |            |          |             |
| break_start     | time           |             |            |          |             |
| break_end       | time           |             |            |          |             |
| break_time      | time           |             |            |          |             |
| total_time      | time           |             |            |          |             |
| created_at      | timestamp      |             |            |          |             |
| updated_at      | timestamp      |             |            |          |             |
| request_status  | varchar(255)   |             |            | ○        |             |
| request_reason  | text           |             |            |          |             |

### attendance_requestsテーブル

| カラム名        | 型             | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY        |
|-----------------|----------------|-------------|------------|----------|-------------------|
| id              | unsigned bigint| ○           |            | ○        |                   |
| attendance_id   | unsigned bigint|             |            | ○        | attendances(id)   |
| user_id         | unsigned bigint|             |            | ○        | users(id)         |
| clock_in        | time           |             |            |          |                   |
| clock_out       | time           |             |            |          |                   |
| break_start     | integer        |             |            |          |                   |
| break_end       | integer        |             |            |          |                   |
| request_reason  | string         |             |            |          |                   |
| created_at      | timestamp      |             |            |          |                   |
| updated_at      | timestamp      |             |            |          |                   |
| request_status  | varchar(255)   |             |            | ○        |                   |

### breaksテーブル

| カラム名        | 型             | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY      |
|-----------------|----------------|-------------|------------|----------|-----------------|
| id              | unsigned bigint| ○           |            | ○        |                 |
| attendance_id   | unsigned bigint|             |            | ○        | attendances(id) |
| break_start     | time           |             |            |          |                 |
| break_end       | time           |             |            |          |                 |
| break_number    | int            |             |            |          |                 |
| created_at      | timestamp      |             |            |          |                 |
| updated_at      | timestamp      |             |            |          |                 |

### attendance_request_breaksテーブル

| カラム名                | 型             | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY               |
|-------------------------|----------------|-------------|------------|----------|---------------------------|
| id                      | unsigned bigint| ○           |            | ○        |                           |
| attendance_requests_id  | unsigned bigint|             |            | ○        | attendance_requests(id)  |
| break_number            | int            |             | ○          |          |                           |
| break_start             | time           |             |            |          |                           |
| break_end               | time           |             |            |          |                           |
| created_at              | timestamp      |             |            |          |                           |
| updated_at              | timestamp      |             |            |          |                           |


### Route / Controller

| 画面名称 | パス | メソッド | ルート先コントローラー | アクション | 認証必須 | 説明 |
|-----------|------|-----------|--------------------------|-------------|-----------|------|
| 会員登録画面（一般ユーザー） | /register | GET / POST | RegisteredUserController | create | ○ | ユーザー情報登録画面 |
| ログイン画面（一般ユーザー） | /login | GET / POST | AuthenticatedSessionController | store | × | ユーザーログイン画面 |
| 出勤登録画面（一般ユーザー） | /user | GET / POST | UserController | index | ○ | ユーザー打刻画面 |
| 勤怠一覧画面（一般ユーザー） | /user/list | GET | UserController | workList | ○ | ユーザー勤怠一覧画面 |
| 勤怠詳細画面（一般ユーザー） | /user/list/detail/{id} | GET / PUT | UserController | userListDetail | ○ | 勤怠詳細画面 |
| 申請一覧画面（一般ユーザー） | /user/request | GET / POST | UserController | userRequest | ○ | 勤怠修正申請画面 |
| ログイン画面（管理者） | /admin/login | GET / POST | AdminLoginController | authenticate | × | 管理者ログイン画面 |
| 勤怠一覧画面（管理者） | /admin/home | GET | AdministratorController | attendanceList | ○ | 全ユーザーの勤怠一覧表示画面 |
| 勤怠詳細画面（管理者） | /admin/attendance/{id} | GET / POST | AdministratorController | listDetail | ○ | 全ユーザーの勤怠詳細画面 |
| スタッフ一覧画面（管理者） | /admin/staff_list | GET | AdministratorController | staffList | ○ | スタッフ一覧画面 |
| スタッフ別勤怠一覧画面（管理者） | /admin/staff_list/{id} | GET | AdministratorController | staffDetail | ○ | スタッフの月間勤怠情報画面 |
| 申請一覧画面（管理者） | /admin/request_list | GET | AdministratorController | requestList | ○ | 全ユーザー勤怠申請一覧 |
| 修正申請承認画面（管理者） | /admin/request_list/{id} | GET / PUT | WorkingStatusController | requestApprove | ○ | 修正承認画面 |

---

### Model

| モデルファイル名 | 説明 |
|------------------|------|
| Admin.php | 管理者の情報を管理するモデル |
| Attendance.php | 勤怠データを管理するモデル |
| AttendanceRequest.php | 勤怠修正情報を管理するモデル |
| User.php | ユーザー情報を管理するモデル |
| AttendanceBreak.php | 休憩データを管理するモデル |
| AttendanceRequestBreak.php | 休憩データの修正を管理するモデル |

---

### View

| 画面名称 | bladeファイル名 |
|-----------|----------------|
| 会員登録画面（一般ユーザー） | user_register.blade.php |
| ログイン画面（一般ユーザー） | user_login.blade.php |
| 出勤登録画面（一般ユーザー） | stamping.blade.php |
| 勤怠一覧画面（一般ユーザー） | work_list.blade.php |
| 勤怠詳細画面（一般ユーザー） | work_list_detail.blade.php |
| 申請一覧画面（一般ユーザー） | work_request.blade.php |
| ログイン画面（管理者） | admin_login.blade.php |
| 勤怠一覧画面（管理者） | attendance_list.blade.php |
| 勤怠詳細画面（管理者） | attendance_detail.blade.php |
| スタッフ一覧画面（管理者） | staff_list.blade.php |
| スタッフ別勤怠一覧画面（管理者） | staff_detail.blade.php |
| 申請一覧画面（管理者） | user_request.blade.php |
| 修正申請承認画面（管理者） | approve.blade.php |

---

### バリデーション

| バリデーションファイル名 | フォーム | ルール |
|--------------------------|----------|--------|
| AdminLoginRequest.php | 管理者ログインフォーム | メールアドレス：必須・パスワード：必須 |
| UserLoginRequest.php | 一般ユーザーログインフォーム | メールアドレス：必須・パスワード：必須（8文字以上） |
| UserRegisterRequest.php | 会員登録フォーム | 名前・メールアドレス：必須、パスワード：必須・重複禁止・8文字以上 |
| AttendanceModifyRequest.php | 勤怠修正申請フォーム | 出退勤：必須・前後関係正しい、休憩：任意・時刻形式、備考：必須・255文字以内 |

---
　　



   










  
