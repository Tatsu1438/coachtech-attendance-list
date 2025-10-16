# coachtech-attendance-list




  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: laravel_db
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_pass


  mysql_test:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: laravel_test_db
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_pass
