# Documentation

## PHP 7.2 upgrade to PHP 7.4

### Installation

```
sudo add-apt-repository ppa:ondrej/php
sudo apt install php7.4
sudo apt install php7.4-fpm
```

`sudo vi /etc/php/7.4/fpm/php.ini` and modify
```
short_open_tag = Off
```
to
```
short_open_tag = On
```
and
```
memory_limit = 128M
```
to
```
memory_limit = 256M
```
and
```
post_max_size = 8M
```
to
```
post_max_size = 64M
```
and
```
upload_max_filesize = 2M
```
to
```
upload_max_filesize = 64M
```
and
```
session.gc_maxlifetime = 1440
```
to
```
session.gc_maxlifetime = 28800
```
and
```
;opcache.memory_consumption=128
```
to
```
opcache.memory_consumption=256
```
and
```
;opcache.max_accelerated_files=10000
```
to
```
opcache.max_accelerated_files=20000
```

Now we test the config:
```
sudo nginx -t
```
and we restart nginx to be sure:
```
sudo service nginx restart
```

Now upgrade the PHP config:
```
sudo vi /etc/nginx/conf.d/upstream-php-fpm.conf
```
and modify
```
upstream php-fpm {
    server unix:/run/php/php7.2-fpm.sock;
}
```
to
```
upstream php-fpm {
    server unix:/run/php/php7.4-fpm.sock;
}
```
and restart nginx again:
```
sudo service nginx restart
```


### Install same extensions as PHP 7.2
```
sudo apt install php7.4-zip
sudo apt install php7.4-xml
sudo apt install php7.4-soap
sudo apt install php7.4-readline
sudo apt install php7.4-opcache
sudo apt install php7.4-mysql
sudo apt install php7.4-mbstring
sudo apt install php7.4-json
sudo apt install php7.4-json
sudo apt install php7.4-gd
sudo apt install php7.4-curl
sudo apt install php7.4-bz2
sudo apt install php7.4-bcmath
sudo apt install php7.4-mcrypt
```
```
sudo apt install php7.4-zip php7.4-xml php7.4-soap php7.4-readline php7.4-opcache php7.4-mysql php7.4-mbstring php7.4-json php7.4-json php7.4-gd php7.4-curl php7.4-bz2 php7.4-bcmath php7.4-bcmath php7.4-mcrypt
sudo systemctl restart nginx
```

### Configure PHP 7.4 FPM settings
```
sudo vi /etc/php/7.4/fpm/pool.d/www.conf
```
change
```
group = www-data
```
to
```
group = webdev
```
and
```
pm = dynamic
```
to
```
pm = static
```
and
```
pm.max_children = 5
```
to
```
pm.max_children = 200
```
and
```
;pm.max_requests = 500
```
to
```
pm.max_requests = 100
```
and
```
;ping.path = /ping
```
to
```
ping.path = /fpm-ping
```
based on this original PHP7.2 config in `/etc/php/7.2/fpm/pool.d/www.conf`:
```
[www]
group = webdev
listen = /run/php/php7.2-fpm.sock
listen.group = www-data
listen.owner = www-data
ping.path = /fpm-ping
;attempting to get more performance.
pm = static
pm.max_children = 200
pm.max_requests = 100
;pm.max_spare_servers = 15
;pm.min_spare_servers = 10
;pm.start_servers = 10
pm.status_path = /fpm-status
user = www-data
```
and restart FPM:
```
sudo systemctl restart php7.4-fpm.service
```

### Configure sudo permissions

In `/etc/sudoers` change the file WITH `sudoedit /etc/sudoers` (so not directly using `vim`!!!) from
```
webdev  ALL=(root) NOPASSWD: /etc/init.d/nginx, /etc/init.d/php7.2-fpm
```
to
```
webdev  ALL=(root) NOPASSWD: /etc/init.d/nginx, /etc/init.d/php7.2-fpm, /etc/init.d/php7.4-fpm
```