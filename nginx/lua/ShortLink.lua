--[[
    Скрипт сокращателя ссылок
]]--

local hex_to_char = function(x)
    return string.char(tonumber(x, 16))
end

local unescape = function(url)
    url = url:gsub("%%(%x%x)", hex_to_char)
    return url
end

local sp = require "provider"

local url = sp:redis():get(sp:getKey('short_link', unescape(ngx.var.request_uri:sub(2))))
sp:close()
if url ~= ngx.null then
    return ngx.exec('/__internal_proxy/'..url)
else
    sp:url():redirect(sp.stop_page)
end


