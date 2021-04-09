Docker LNMP
===========

Docker environment with bundled Linux Nginx MariaDB PHP-FPM environment. For development and production usage.

Features
--------

-	**PHP-FPM 7.2**
-	ionCube PHP Loader
-	Zend OPcache
-	Xdebug (Set breakpoint in code with function `xdebug_break()`\)
-	Composer
-	Imagemagick
-	**MariaDB** (Container is stateless and data is mapped to `./services/mariadb/data` directory)
-	phpMyAdmin
-	Default locale `de_DE.UTF-8`
-	Default localtime `Europe/Berlin`

Configuration
-------------

-	Rename `example.env` to `.env` and edit to your needs
-	Remove all files and directories from `./htdocs`
-	Download and copy your web project to `./htdocs`
-	Configure your web project with the defined ENV vars (e.g. `getenv('MYSQL_PASSWORD')`)

Usage
-----

### Docker compose

`@see` https://docs.docker.com/compose/reference/

### Build

```
docker-compose build
```

### Run

```
docker-compose up
```

or

```
docker-compose up -d
```

> Param `-d` runs container in background (detach)

### Bash into

> Replace `docker-lnmp_*` with your container names

```
$ docker exec -ti docker-lnmp_php_1 /bin/bash
$ docker exec -ti docker-lnmp_nginx_1 /bin/bash
```

Database backup

```
$ docker exec docker-lnmp_mariadb_1 sh -c 'exec mysqldump --all-databases -uroot -p"$MYSQL_ROOT_PASSWORD"' > /some/path/on/your/host/all-databases.sql
```

### Stop

```
docker-compose down
```

> To stop all containers AND kill all volumes `docker-compose down --volumes`

### Access

Web

http://localhost:8080/

phpMyAdmin

http://localhost:8282/

MariaDB

Usage of ENV variables. For example see `./htdocs/test/env.php` and config section of this page.
