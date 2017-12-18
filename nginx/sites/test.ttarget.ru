server {
    listen 80; ## listen for ipv4
    server_name test.ttarget.ru;

    root   /home/test.ttarget.ru/htdocs;
    index  index.php;

    charset utf-8;

    access_log /var/log/nginx/test-ttarget-access.log;
    error_log  /var/log/nginx/test-ttarget-error.log crit;

    location = /test-ttarget.html {
        expires off;
        access_log off;
        log_not_found off;
        root /home;
        index test-ttarget.html;
    }

    location = /old-test-ttarget.html {
        expires off;
        access_log off;
        log_not_found off;
        root /home;
        index old-test-ttarget.html;
    }

    location = /show {
        expires off;
        access_log off;
        log_not_found off;
        default_type 'application/javascript';
        lua_code_cache on;
        content_by_lua_file /home/test.ttarget.ru/nginx/lua/show.lua;
    }

    location = /get/ {
        expires off;
        access_log off;
        log_not_found off;
        default_type 'application/javascript';
        lua_code_cache on;
        set $renderer_version 0;
        content_by_lua_file /home/test.ttarget.ru/nginx/lua/get.lua;
    }

    location = /get.js {
        expires off;
        access_log off;
        log_not_found off;
        default_type 'application/javascript';
        lua_code_cache on;
        set $renderer_version 1;
        content_by_lua_file /home/test.ttarget.ru/nginx/lua/get.lua;
    }
	
    location /go {
        access_log off;
        log_not_found off;
        default_type 'text/html';
        lua_code_cache on;
        content_by_lua_file /home/test.ttarget.ru/nginx/lua/go.lua;
    }
	
	location /og {
        access_log off;
        log_not_found off;
        default_type 'text/html';
        lua_code_cache on;
        content_by_lua_file /home/test.ttarget.ru/nginx/lua/offer_go.lua;
    }

    location = /cpa.js {
        access_log off;
        log_not_found off;
        expires off;
        default_type 'application/javascript';
        lua_code_cache on;
        content_by_lua_file /home/ttarget/nginx/lua/track.lua;
    }

    location = /track.js {
        access_log off;
        log_not_found off;
        expires off;
        default_type 'application/javascript';
        lua_code_cache on;
        content_by_lua_file /home/ttarget/nginx/lua/track.lua;
    }

    location = /cpa/click.js {
        access_log off;
        log_not_found off;
        lua_code_cache on;
        default_type 'application/javascript';
        content_by_lua_file /home/ttarget/nginx/lua/click.lua;
    }

    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    # Disable logging for robots.txt
    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }

    location ~ /\. {
        access_log off;
        log_not_found off;
        deny all;
    }

    location ~ /(framework|nbproject) {
        deny all;
        access_log off;
        log_not_found off;
    }

    location ~ /themes/\w+/views {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Статическиое наполнение отдает сам nginx
    # back-end этим заниматься не должен
    location ~* \.(jpg|jpeg|gif|png|ico|css|bmp|swf|js|txt)$ {
        access_log        off;
        log_not_found     off;
        expires           1d;
    }

    location / {
        try_files       $uri $uri/ /index.php;

        if (!-e $request_filename){
            rewrite (.*) /index.php;
        }

    }

    location ~ \.php$ {

        try_files $uri =403;
        fastcgi_buffers 256 8k; # Sets the buffer size to 4k + 256 * 4k = 1028k

        fastcgi_pass    unix:/var/run/test-php-fpm.socket;
        fastcgi_index   index.php;

        include         fastcgi_params;
    }
}

