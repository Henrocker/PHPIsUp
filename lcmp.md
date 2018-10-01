# LCMP Server installieren (Linux, Caddy-Webserver, MySQL & PHP)

### Systemumgebung

  - Ubuntu 16.04/368
  - SSH-Server OpenSSH

## Abhängigkeiten installieren

Zu aller erst werden die Paketlisten des Paketmanagers **apt** aktualisiert.
```sh
$ apt update && apt upgrade -y
```

Anschließend werden die Abhängigkeiten und die benötigte Software heruntergeladen.
```sh
$ apt install build-essential git unzip zip sudo nano htop jnettop screen ca-certificates \
php7.0-fpm php7.0-mbstring php7.0-curl php7.0-xml php7.0-gd php7.0-mysql mysql-server
```

Bei der Passwortabfrage des Paketmanagers für MySQL wird das root Passwort vergeben.

## Umgebung für externes Netzwerk konfigurieren

Nun wird die Linuxumgebung für das externe Netzwerk vorbereitet.
```sh
$ nano /etc/environment

PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games:$GOROOT/bin"
http_proxy=http://172.17.1.1:3128
https_proxy=https://172.17.1.1:3128
no_proxy=localhost
soap_use_proxy=on
```

Danach wird der Server einmal neugestartet mit dem Befehl: **reboot**.

## GoLang installieren

Da der Webserver **Caddy** in der Programmiersprache _go_ geschrieben wurde, wird die Programmierumgebung von _go_ benötigt.
```sh
$ mkdir go-files && cd go-files
$ wget https://dl.google.com/go/go1.10.4.linux-386.tar.gz
$ tar -xzvf go1.10.4.linux-386.tar.gz
$ rm go1.10.4.linux-386.tar.gz
```

Nun, da die Programmierumgebung unter dem Ordner _~/go-files_ installiert wurde, wird _go_ systemweit erreichbar konfiguriert.
```sh
$ nano ~/.profile

...
GOROOT=/root/go-files/go
GOPATH=$HOME/go
PATH=$PATH:$GOROOT/bin
```

Danach muss die Profildatei des Users einmalig neu indexiert werden.
```sh
$ source ~/.profile
```

Und anschließend kann die globale Reichweite von _go_ getestet werden.
```sh
$ go version
go version go1.10.4 linux/386
```

## Caddy Webserver installieren

Nun wird der Caddy Webserver heruntergeladen, konfiguriert und installiert.

```sh
$ go get github.com/mholt/caddy
$ go get github.com/caddyserver/builds
$ cd $GOPATH/src/github.com/mholt/caddy/caddy
$ go run build.go
```

Nun befindet sich im Ordner eine Executable namens **caddy**. Diese wird an einen neuen Ort kopiert, wo sie später die Website herholt.
In diesem Zuge werden die _Konfigurationsdatei_ von Caddy, die Ordner _log_ und _html_, sowie die Error- und Access-Log-Dateien unter _log_ erstellt.
```sh
mkdir /etc/caddy
cp caddy /etc/caddy
cd /etc/caddy
mkdir logs
mkdir html
touch logs/access.log
touch logs/error.log
touch Caddyfile
```

Nun kann die Konfigurationsdatei vom Caddy Webserver editiert werden.
```sh
$ nano Caddyfile

:80  {
        tls off
        redir / https://bktm.henrock.net{uri}
}

https://bktm.henrock.net {
        tls /etc/caddy/certificate.crt /etc/caddy/privateKey.key
        root /etc/caddy/html
        fastcgi / unix:/run/php/php7.0-fpm.sock php {
                root /etc/caddy/html
        }
        timeouts none
        ext .html .htm .php
        index index.html index.htm index.php
        errors /etc/caddy/logs/error.log
        log / /etc/caddy/logs/access.log
        rewrite {
        	r ^/index.php/.*$
        	to /index.php?{query}
    	}
    	redir /.well-known/carddav /remote.php/carddav 301
    	redir /.well-known/caldav /remote.php/caldav 301
    	rewrite {
        	r ^/remote.php/(webdav|caldav|carddav|dav)(\/?)$
        	to /remote.php/{1}
    	}
        rewrite {
        	r ^/remote.php/(webdav|caldav|carddav|dav)/(.+?)(\/?)$
        	to /remote.php/{1}/{2}
    	}
    	status 403 {
                /.htacces
                /data
                /config
                /db_structure
                /.xml
                /README
    	}
}
```

Der Caddy Webserver ist zu diesem Zeitpunkt vollständig eingerichtet und funktionsfähig und kann gestartet werden.
```sh
$ cd /etc/caddy
$ screen ./caddy
$ STRG+A & d
```

## PHPMyAdmin installieren

PHPMyAdmin ist eine grafische Oberfläche für den MySQL Server und vereinfacht das Anlegen und Verwalten von Datenbanken.

```sh
$ cd /etc/caddy/html
$ wget https://files.phpmyadmin.net/phpMyAdmin/4.8.3/phpMyAdmin-4.8.3-all-languages.zip
$ unzip phpMyAdmin-4.8.3-all-languages.zip
$ rm phpMyAdmin-4.8.3-all-languages.zip
$ mv phpMyAdmin-4.8.3-all-languages phpmyadmin
```

Anschließend werden die Rechte für den Ordner _phpmyadmin_ gesetzt.
```sh
$ chmod -R 755 phpmyadmin/ && chown -R www-data:www-data phpmyadmin/
```

Besucht man nun _https://bktm.henrock.net/phpmyadmin_, gelangt man zum Webinterface von _PHPMyAdmin_ und kann sich mit seinen, 
bei der Installation von MySQL eingegebenen Kredenzien Login-Daten, anmelden.

## Wordpress CMS installieren

Als Letztes wird Wordpress installiert.

```sh
$ cd /etc/caddy/html
$ wget https://de.wordpress.org/wordpress-4.9.8-de_DE.zip
$ unzip wordpress-4.9.8-de_DE.zip
$ rm wordpress-4.9.8-de_DE.zip
$ cd wordpress
$ cp -r * ..
$ rm -r wordpress
```

Besucht man nun _https://bktm.henrock.net/wordpress_, so gelangt man zur Wordpress Installationsseite, wo Wordpress via grafischer Oberfläche
eingerichtet und installiert wird.
Sobald erledigt, findet man die Wordpress Seite unter _https://bktm.henrock.net/wordpress_.

Von hier aus kann die Wordpressseite nach Belieben angepasst werden.

© by Henrik Neef
