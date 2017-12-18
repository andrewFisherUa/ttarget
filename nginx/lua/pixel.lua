local sp = require "provider"

local uid = sp:session():start();

local sign = ngx.crc32_long(
    ngx.var.remote_addr:gsub("^(%d+.%d+.%d+).%d+$", "%1")
        .. (ngx.var.http_referer or '')
        .. (ngx.var.http_user_agent or '')
        .. uid
        .. sp.yandex_rtb_secret
)
ngx.redirect(sp.yandex_cookie_match_url..uid..'?sign='..sign)
