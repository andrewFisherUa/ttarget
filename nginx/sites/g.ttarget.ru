server {
    listen 80; ## listen for ipv4
    server_name g.ttarget.ru;

    root   /home/ttarget/htdocs;
    index  index.php;

    charset utf-8;

    access_log /var/log/nginx/g-ttarget-access.log;
    error_log  /var/log/nginx/g-ttarget-error.log;

    location / {
        expires off;
        access_log off;
        log_not_found off;
        default_type 'text/html';
        lua_code_cache on;
        content_by_lua_file /var/www/nginx/lua/ShortLink.lua;
    }

    location ~ ^/__internal_proxy/(.*) {
        internal;
        proxy_no_cache     1;
        proxy_cache_bypass 1;
        proxy_pass         $scheme://127.0.0.1/$1$is_args$args;
        proxy_set_header   Host    tt.ttarget.ru;
		proxy_set_header   X-Real-IP    $remote_addr;
    }
}